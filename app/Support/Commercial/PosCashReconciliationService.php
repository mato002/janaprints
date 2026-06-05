<?php

namespace App\Support\Commercial;

use App\Enums\PosReconciliationAction;
use App\Enums\PosReconciliationStatus;
use App\Enums\PosVarianceType;
use App\Models\Pos\PosCashReconciliation;
use App\Models\Pos\PosCashReconciliationLog;
use App\Models\Pos\PosSession;
use App\Support\Accounting\PosAccountingPostingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PosCashReconciliationService
{
    public function __construct(
        protected PosSessionService $sessions,
        protected PosAccountingPostingService $accounting,
    ) {}

    public function nextReconciliationNumber(int $companyId): string
    {
        $prefix = 'REC-'.now()->format('Ymd').'-';
        $last = PosCashReconciliation::query()
            ->where('company_id', $companyId)
            ->where('reconciliation_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('reconciliation_number');

        $sequence = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function createFromSession(PosSession $session, int $actorId): PosCashReconciliation
    {
        if (PosCashReconciliation::query()->where('pos_session_id', $session->id)->exists()) {
            return PosCashReconciliation::query()->where('pos_session_id', $session->id)->firstOrFail();
        }

        $metrics = $this->sessions->sessionMetrics($session);
        $variance = round((float) $session->variance, 2);

        return DB::transaction(function () use ($session, $metrics, $variance, $actorId) {
            $reconciliation = PosCashReconciliation::query()->create([
                'company_id' => $session->company_id,
                'branch_id' => $session->branch_id,
                'pos_session_id' => $session->id,
                'cashier_id' => $session->cashier_id,
                'reconciliation_number' => $this->nextReconciliationNumber((int) $session->company_id),
                'opening_float' => $session->opening_float,
                'cash_sales' => $metrics['cash_sales'],
                'mpesa_sales' => $metrics['mpesa_sales'],
                'card_sales' => $metrics['card_sales'],
                'refunds_count' => $metrics['refunds'],
                'refund_total' => $metrics['refund_total'],
                'expected_cash' => $session->expected_cash,
                'actual_cash' => $session->actual_cash,
                'variance' => $variance,
                'variance_type' => PosVarianceType::fromAmount($variance),
                'status' => PosReconciliationStatus::Pending,
                'notes' => $session->closing_notes,
            ]);

            $this->log($reconciliation, $actorId, PosReconciliationAction::Created, __('Reconciliation created from closed session.'));

            return $reconciliation;
        });
    }

    public function submit(PosCashReconciliation $reconciliation, int $userId, ?string $notes = null): PosCashReconciliation
    {
        if ($reconciliation->status !== PosReconciliationStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => __('Only pending reconciliations can be submitted.'),
            ]);
        }

        return DB::transaction(function () use ($reconciliation, $userId, $notes) {
            $status = $reconciliation->variance_type === PosVarianceType::Balanced
                ? PosReconciliationStatus::Balanced
                : PosReconciliationStatus::VarianceFound;

            $reconciliation->update([
                'status' => $status,
                'submitted_by' => $userId,
                'submitted_at' => now(),
                'notes' => $notes ?? $reconciliation->notes,
            ]);

            $this->log($reconciliation, $userId, PosReconciliationAction::Submitted, $notes);

            return $reconciliation->fresh(['cashier', 'branch', 'session']);
        });
    }

    public function review(PosCashReconciliation $reconciliation, int $userId, ?string $notes = null): PosCashReconciliation
    {
        if (! $reconciliation->status->awaitsApproval()) {
            throw ValidationException::withMessages([
                'status' => __('Only submitted reconciliations can be reviewed.'),
            ]);
        }

        if ($reconciliation->reviewed_at !== null) {
            throw ValidationException::withMessages([
                'status' => __('This reconciliation has already been reviewed.'),
            ]);
        }

        return DB::transaction(function () use ($reconciliation, $userId, $notes) {
            $reconciliation->update([
                'reviewed_by' => $userId,
                'reviewed_at' => now(),
                'review_notes' => $notes,
            ]);

            $this->log($reconciliation, $userId, PosReconciliationAction::Reviewed, $notes);

            return $reconciliation->fresh(['cashier', 'branch', 'session', 'reviewer']);
        });
    }

    public function approve(PosCashReconciliation $reconciliation, int $userId, ?string $notes = null): PosCashReconciliation
    {
        if (! $reconciliation->status->awaitsApproval()) {
            throw ValidationException::withMessages([
                'status' => __('Only balanced or variance-found reconciliations can be approved.'),
            ]);
        }

        if ($reconciliation->reviewed_at === null) {
            throw ValidationException::withMessages([
                'status' => __('Supervisor review is required before approval.'),
            ]);
        }

        return DB::transaction(function () use ($reconciliation, $userId, $notes) {
            $reconciliation->update([
                'status' => PosReconciliationStatus::Approved,
                'approved_by' => $userId,
                'approved_at' => now(),
                'approval_notes' => $notes,
            ]);

            $this->log($reconciliation, $userId, PosReconciliationAction::Approved, $notes);

            $reconciliation = $reconciliation->fresh(['cashier', 'branch', 'session', 'approver']);
            $this->accounting->postVariance($reconciliation, $userId);

            return $reconciliation->fresh(['cashier', 'branch', 'session', 'approver']);
        });
    }

    public function reject(PosCashReconciliation $reconciliation, int $userId, string $reason): PosCashReconciliation
    {
        if (! $reconciliation->status->awaitsApproval()) {
            throw ValidationException::withMessages([
                'status' => __('Only balanced or variance-found reconciliations can be rejected.'),
            ]);
        }

        if ($reconciliation->reviewed_at === null) {
            throw ValidationException::withMessages([
                'status' => __('Supervisor review is required before rejection.'),
            ]);
        }

        return DB::transaction(function () use ($reconciliation, $userId, $reason) {
            $reconciliation->update([
                'status' => PosReconciliationStatus::Rejected,
                'rejected_by' => $userId,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ]);

            $this->log($reconciliation, $userId, PosReconciliationAction::Rejected, $reason);

            return $reconciliation->fresh(['cashier', 'branch', 'session', 'rejector']);
        });
    }

    /**
     * @return array{
     *     today_count: int,
     *     pending_reviews: int,
     *     variance_cases: int,
     *     approved_today: int,
     *     total_cash: float,
     *     total_mpesa: float,
     *     total_card: float
     * }
     */
    public function dashboardStats(int $companyId, ?int $branchId): array
    {
        $base = PosCashReconciliation::query()->where('company_id', $companyId);
        if ($branchId !== null) {
            $base->where('branch_id', $branchId);
        }

        $today = (clone $base)->whereDate('created_at', today());

        return [
            'today_count' => (int) $today->count(),
            'pending_reviews' => (int) (clone $base)
                ->whereIn('status', [PosReconciliationStatus::Balanced, PosReconciliationStatus::VarianceFound])
                ->whereNull('reviewed_at')
                ->count(),
            'variance_cases' => (int) (clone $base)
                ->where('status', PosReconciliationStatus::VarianceFound)
                ->whereNull('approved_at')
                ->whereNull('rejected_at')
                ->count(),
            'approved_today' => (int) (clone $base)
                ->where('status', PosReconciliationStatus::Approved)
                ->whereDate('approved_at', today())
                ->count(),
            'total_cash' => round((float) (clone $today)->sum('cash_sales'), 2),
            'total_mpesa' => round((float) (clone $today)->sum('mpesa_sales'), 2),
            'total_card' => round((float) (clone $today)->sum('card_sales'), 2),
        ];
    }

    protected function log(
        PosCashReconciliation $reconciliation,
        int $userId,
        PosReconciliationAction $action,
        ?string $notes = null,
        ?array $metadata = null,
    ): void {
        PosCashReconciliationLog::query()->create([
            'pos_cash_reconciliation_id' => $reconciliation->id,
            'company_id' => $reconciliation->company_id,
            'user_id' => $userId,
            'action' => $action,
            'notes' => $notes,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}

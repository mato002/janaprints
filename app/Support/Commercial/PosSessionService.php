<?php

namespace App\Support\Commercial;

use App\Enums\PosSessionStatus;
use App\Models\Pos\PosSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PosSessionService
{
    public function __construct(
        protected PosSessionCalculator $calculator,
        protected PosSessionValidationService $validation,
        protected PosSessionVarianceService $variance,
    ) {}

    public function nextSessionNumber(int $companyId): string
    {
        $prefix = 'SES-'.now()->format('Ymd').'-';
        $last = PosSession::query()
            ->where('company_id', $companyId)
            ->where('session_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('session_number');

        $sequence = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function openSession(
        int $companyId,
        int $branchId,
        int $cashierId,
        float $openingFloat,
        float $openingCash,
        int $openedBy,
        ?string $notes = null,
        ?string $terminal = null,
    ): PosSession {
        return DB::transaction(function () use ($companyId, $branchId, $cashierId, $openingFloat, $openingCash, $openedBy, $notes, $terminal) {
            $this->validation->assertNoDuplicateActiveSession($companyId, $branchId, $cashierId);

            return PosSession::query()->create([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'cashier_id' => $cashierId,
                'session_number' => $this->nextSessionNumber($companyId),
                'terminal' => $terminal ?: config('pos.default_terminal'),
                'opening_float' => $openingFloat,
                'opening_cash' => $openingCash,
                'status' => PosSessionStatus::Open,
                'opened_at' => now(),
                'opened_by' => $openedBy,
                'opening_notes' => $notes,
            ]);
        });
    }

    public function closureGovernance(PosSession $session): array
    {
        return $this->validation->closureGovernance($session);
    }

    public function assertSessionReadyToClose(PosSession $session): void
    {
        $this->validation->assertSessionReadyToClose($session);
    }

    public function closeSession(PosSession $session, float $actualCash, int $closedBy, ?string $notes = null): PosSession
    {
        if ($session->status !== PosSessionStatus::Open) {
            throw ValidationException::withMessages([
                'status' => __('Only open sessions can be closed.'),
            ]);
        }

        $this->validation->assertSessionReadyToClose($session);

        return DB::transaction(function () use ($session, $actualCash, $closedBy, $notes) {
            $session->update(['status' => PosSessionStatus::Closing]);

            $metrics = $this->calculator->sessionMetrics($session);
            $expectedCash = $metrics['expected_closing_cash'];
            $resolution = $this->variance->resolveCloseStatus($expectedCash, $actualCash);

            $session->update([
                'expected_cash' => $expectedCash,
                'expected_mpesa' => $metrics['expected_mpesa'],
                'expected_card' => $metrics['expected_card'],
                'expected_bank' => $metrics['expected_bank'],
                'expected_total' => $metrics['expected_total'],
                'actual_cash' => $actualCash,
                'variance' => $resolution['variance'],
                'variance_requires_approval' => $resolution['requires_approval'],
                'status' => $resolution['status'],
                'closed_at' => now(),
                'closed_by' => $closedBy,
                'closing_notes' => $notes,
            ]);

            return $session->fresh(['cashier', 'branch', 'closer', 'varianceApprover']);
        });
    }

    public function approveVariance(PosSession $session, int $approvedBy): PosSession
    {
        return $this->variance->approveVariance($session, $approvedBy);
    }

    public function activeSessionForCashier(int $companyId, int $branchId, int $cashierId): ?PosSession
    {
        return PosSession::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('cashier_id', $cashierId)
            ->whereIn('status', [PosSessionStatus::Open, PosSessionStatus::Suspended])
            ->first();
    }

    public function requireOpenSession(int $companyId, int $branchId, int $cashierId): PosSession
    {
        return $this->validation->requireOpenSession($companyId, $branchId, $cashierId);
    }

    public function assertSessionAcceptsSales(PosSession $session): void
    {
        $this->validation->assertSessionAcceptsSales($session);
    }

    public function expectedClosingCash(PosSession $session): float
    {
        return $this->calculator->expectedClosingCash($session);
    }

    public function sessionMetrics(PosSession $session): array
    {
        return $this->calculator->sessionMetrics($session);
    }

    public function dashboardStats(int $companyId, ?int $branchId): array
    {
        return $this->calculator->dashboardStats($companyId, $branchId);
    }

    /**
     * @return array{
     *     session: PosSession|null,
     *     metrics: array<string, mixed>|null
     * }
     */
    public function currentCashierSessionWidget(int $companyId, int $branchId, int $cashierId): array
    {
        $session = $this->activeSessionForCashier($companyId, $branchId, $cashierId);

        if ($session === null) {
            return ['session' => null, 'metrics' => null];
        }

        return [
            'session' => $session,
            'metrics' => $this->calculator->sessionMetrics($session),
        ];
    }
}

<?php

namespace App\Support\Commercial;

use App\Enums\PosPaymentMethod;
use App\Enums\PosReconciliationStatus;
use App\Enums\PosRefundMethod;
use App\Enums\PosReturnStatus;
use App\Enums\PosSaleStatus;
use App\Enums\PosSessionStatus;
use App\Enums\PosVarianceType;
use App\Models\Inventory\InventoryMovement;
use App\Models\Pos\PosCashReconciliation;
use App\Models\Pos\PosPayment;
use App\Models\Pos\PosReturn;
use App\Models\Pos\PosSale;
use App\Models\Pos\PosSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PosCertificationService
{
    public function __construct(
        protected PosSessionService $sessions,
        protected PosInventoryService $inventory,
    ) {}

    /**
     * @return array{
     *     scope: PosCertificationScope,
     *     verdict: string,
     *     passed: bool,
     *     score: int,
     *     domains: list<array<string, mixed>>,
     *     certified_at: string
     * }
     */
    public function certify(PosCertificationScope $scope): array
    {
        $domains = [
            $this->inventoryTruth($scope),
            $this->accountingTruth($scope),
            $this->cashTruth($scope),
            $this->returnsTruth($scope),
            $this->sessionTruth($scope),
            $this->branchCompliance($scope),
        ];

        $passedCount = collect($domains)->where('passed', true)->count();
        $score = (int) round(($passedCount / count($domains)) * 100);
        $passed = $passedCount === count($domains);

        return [
            'scope' => $scope,
            'verdict' => $passed ? 'PASS' : 'FAIL',
            'passed' => $passed,
            'score' => $score,
            'domains' => $domains,
            'certified_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function inventoryTruth(PosCertificationScope $scope): array
    {
        $unpostedSales = (int) $this->salesQuery($scope)
            ->where('status', PosSaleStatus::Paid)
            ->whereHas('items', fn (Builder $q) => $q->whereNotNull('inventory_item_id'))
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('inventory_movements')
                    ->whereColumn('inventory_movements.reference_id', 'pos_sales.id')
                    ->where('inventory_movements.reference_type', PosInventoryService::REFERENCE_TYPE_POS_SALE);
            })
            ->count();

        $unpostedReturns = (int) $this->returnsQuery($scope)
            ->where('status', PosReturnStatus::Completed)
            ->whereHas('items.saleItem', fn (Builder $q) => $q->whereNotNull('inventory_item_id'))
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('inventory_movements')
                    ->whereColumn('inventory_movements.reference_id', 'pos_returns.id')
                    ->where('inventory_movements.reference_type', PosInventoryService::REFERENCE_TYPE_POS_RETURN);
            })
            ->count();

        $invalidMovements = (int) $this->salesQuery($scope)
            ->whereIn('status', [PosSaleStatus::Held, PosSaleStatus::Cancelled, PosSaleStatus::Draft])
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('inventory_movements')
                    ->whereColumn('inventory_movements.reference_id', 'pos_sales.id')
                    ->where('inventory_movements.reference_type', PosInventoryService::REFERENCE_TYPE_POS_SALE);
            })
            ->count();

        $issues = array_filter([
            $unpostedSales > 0 ? __(':count paid sale(s) missing stock deduction.', ['count' => $unpostedSales]) : null,
            $unpostedReturns > 0 ? __(':count completed return(s) missing stock restoration.', ['count' => $unpostedReturns]) : null,
            $invalidMovements > 0 ? __(':count held/draft/cancelled sale(s) with invalid stock movement.', ['count' => $invalidMovements]) : null,
        ]);

        $passed = $unpostedSales === 0 && $unpostedReturns === 0 && $invalidMovements === 0;

        return $this->domainResult(
            key: 'inventory_truth',
            label: __('Inventory Truth'),
            passed: $passed,
            metrics: [
                __('Unposted paid sales') => $unpostedSales,
                __('Unposted returns') => $unpostedReturns,
                __('Invalid movements') => $invalidMovements,
            ],
            issues: array_values($issues),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function accountingTruth(PosCertificationScope $scope): array
    {
        $unpostedPayments = (int) PosPayment::query()
            ->whereHas('sale', fn (Builder $q) => $this->applySaleScope($q, $scope)
                ->where('status', PosSaleStatus::Paid))
            ->whereIn('payment_method', [
                PosPaymentMethod::Cash,
                PosPaymentMethod::Mpesa,
                PosPaymentMethod::Card,
            ])
            ->whereNull('posted_journal_id')
            ->count();

        $unpostedReturns = (int) $this->returnsQuery($scope)
            ->where('status', PosReturnStatus::Completed)
            ->where('refund_method', '!=', PosRefundMethod::NoRefund)
            ->where('refund_amount', '>', 0)
            ->whereNull('posted_journal_id')
            ->count();

        $unpostedVariances = 0;
        if (Schema::hasTable('pos_cash_reconciliations') && Schema::hasColumn('pos_cash_reconciliations', 'posted_journal_id')) {
            $unpostedVariances = (int) $this->reconciliationQuery($scope)
                ->where('status', PosReconciliationStatus::Approved)
                ->where('variance_type', '!=', PosVarianceType::Balanced)
                ->where('variance', '!=', 0)
                ->whereNull('posted_journal_id')
                ->count();
        }

        $issues = array_filter([
            $unpostedPayments > 0 ? __(':count payment(s) missing GL journal.', ['count' => $unpostedPayments]) : null,
            $unpostedReturns > 0 ? __(':count return(s) missing GL reversal.', ['count' => $unpostedReturns]) : null,
            $unpostedVariances > 0 ? __(':count variance(s) missing GL posting.', ['count' => $unpostedVariances]) : null,
        ]);

        $passed = $unpostedPayments === 0 && $unpostedReturns === 0 && $unpostedVariances === 0;

        return $this->domainResult(
            key: 'accounting_truth',
            label: __('Accounting Truth'),
            passed: $passed,
            metrics: [
                __('Unposted payments') => $unpostedPayments,
                __('Unposted returns') => $unpostedReturns,
                __('Unposted variances') => $unpostedVariances,
            ],
            issues: array_values($issues),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function cashTruth(PosCertificationScope $scope): array
    {
        $openWithBlockers = 0;
        $openSessions = $this->sessionQuery($scope)
            ->where('status', PosSessionStatus::Open)
            ->get();

        foreach ($openSessions as $session) {
            if (! $this->sessions->closureGovernance($session)['can_close']) {
                $openWithBlockers++;
            }
        }

        $pendingReconciliations = 0;
        $unapprovedClosed = 0;
        $closedWithoutRecon = 0;

        if (Schema::hasTable('pos_cash_reconciliations')) {
            $pendingReconciliations = (int) $this->reconciliationQuery($scope)
                ->whereIn('status', [
                    PosReconciliationStatus::Pending,
                    PosReconciliationStatus::Balanced,
                    PosReconciliationStatus::VarianceFound,
                ])
                ->count();

            $closedQuery = $this->sessionQuery($scope)
                ->where('status', PosSessionStatus::Closed)
                ->whereBetween(DB::raw('DATE(closed_at)'), [$scope->fromDate->toDateString(), $scope->toDate->toDateString()]);

            $unapprovedClosed = (int) (clone $closedQuery)
                ->whereHas('cashReconciliation', fn (Builder $q) => $q->where('status', '!=', PosReconciliationStatus::Approved))
                ->count();

            $closedWithoutRecon = (int) (clone $closedQuery)
                ->whereDoesntHave('cashReconciliation')
                ->count();
        }

        $issues = array_filter([
            $openWithBlockers > 0 ? __(':count open session(s) have unresolved cash work.', ['count' => $openWithBlockers]) : null,
            $pendingReconciliations > 0 ? __(':count reconciliation(s) awaiting approval.', ['count' => $pendingReconciliations]) : null,
            $unapprovedClosed > 0 ? __(':count closed session(s) with unapproved reconciliation.', ['count' => $unapprovedClosed]) : null,
            $closedWithoutRecon > 0 ? __(':count closed session(s) missing reconciliation record.', ['count' => $closedWithoutRecon]) : null,
        ]);

        $passed = $openWithBlockers === 0 && $pendingReconciliations === 0 && $unapprovedClosed === 0 && $closedWithoutRecon === 0;

        return $this->domainResult(
            key: 'cash_truth',
            label: __('Cash Truth'),
            passed: $passed,
            metrics: [
                __('Open sessions with blockers') => $openWithBlockers,
                __('Pending reconciliations') => $pendingReconciliations,
                __('Unapproved closed sessions') => $unapprovedClosed,
                __('Closed without reconciliation') => $closedWithoutRecon,
            ],
            issues: array_values($issues),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function returnsTruth(PosCertificationScope $scope): array
    {
        $pendingReturns = (int) $this->returnsQuery($scope)
            ->where('status', PosReturnStatus::Pending)
            ->count();

        $approvedNotCompleted = (int) $this->returnsQuery($scope)
            ->where('status', PosReturnStatus::Approved)
            ->count();

        $legacyRefunds = (int) $this->salesQuery($scope)
            ->where('status', PosSaleStatus::Refunded)
            ->whereDoesntHave('returns')
            ->count();

        $issues = array_filter([
            $pendingReturns > 0 ? __(':count return(s) pending approval.', ['count' => $pendingReturns]) : null,
            $approvedNotCompleted > 0 ? __(':count return(s) approved but not completed.', ['count' => $approvedNotCompleted]) : null,
            $legacyRefunds > 0 ? __(':count legacy refunded sale(s) without return record.', ['count' => $legacyRefunds]) : null,
        ]);

        $passed = $pendingReturns === 0 && $approvedNotCompleted === 0 && $legacyRefunds === 0;

        return $this->domainResult(
            key: 'returns_truth',
            label: __('Returns Truth'),
            passed: $passed,
            metrics: [
                __('Pending returns') => $pendingReturns,
                __('Approved not completed') => $approvedNotCompleted,
                __('Legacy refunds') => $legacyRefunds,
            ],
            issues: array_values($issues),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function sessionTruth(PosCertificationScope $scope): array
    {
        $openSessions = (int) $this->sessionQuery($scope)
            ->where('status', PosSessionStatus::Open)
            ->count();

        $heldInOpenSessions = (int) PosSale::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn (Builder $q) => $q->where('branch_id', $scope->branchId))
            ->where('status', PosSaleStatus::Held)
            ->whereHas('session', fn (Builder $q) => $q->where('status', PosSessionStatus::Open))
            ->count();

        $draftInOpenSessions = (int) PosSale::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn (Builder $q) => $q->where('branch_id', $scope->branchId))
            ->where('status', PosSaleStatus::Draft)
            ->whereHas('session', fn (Builder $q) => $q->where('status', PosSessionStatus::Open))
            ->count();

        $paidWithoutSession = (int) $this->salesQuery($scope)
            ->where('status', PosSaleStatus::Paid)
            ->whereNull('pos_session_id')
            ->count();

        $issues = array_filter([
            $heldInOpenSessions > 0 ? __(':count held sale(s) in open sessions.', ['count' => $heldInOpenSessions]) : null,
            $draftInOpenSessions > 0 ? __(':count draft sale(s) in open sessions.', ['count' => $draftInOpenSessions]) : null,
            $paidWithoutSession > 0 ? __(':count paid sale(s) not linked to a session.', ['count' => $paidWithoutSession]) : null,
        ]);

        $passed = $heldInOpenSessions === 0 && $draftInOpenSessions === 0 && $paidWithoutSession === 0;

        return $this->domainResult(
            key: 'session_truth',
            label: __('Session Truth'),
            passed: $passed,
            metrics: [
                __('Open sessions') => $openSessions,
                __('Held in open sessions') => $heldInOpenSessions,
                __('Draft in open sessions') => $draftInOpenSessions,
                __('Paid without session') => $paidWithoutSession,
            ],
            issues: array_values($issues),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function branchCompliance(PosCertificationScope $scope): array
    {
        $salesMissingBranch = (int) PosSale::query()
            ->where('company_id', $scope->companyId)
            ->whereBetween('sale_date', [$scope->fromDate->toDateString(), $scope->toDate->toDateString()])
            ->whereNull('branch_id')
            ->count();

        $sessionsMissingBranch = (int) PosSession::query()
            ->where('company_id', $scope->companyId)
            ->whereNull('branch_id')
            ->count();

        $crossBranchSales = 0;
        if ($scope->branchId !== null) {
            $crossBranchSales = (int) $this->salesQuery($scope)
                ->where('branch_id', '!=', $scope->branchId)
                ->count();
        }

        $issues = array_filter([
            $salesMissingBranch > 0 ? __(':count sale(s) missing branch assignment.', ['count' => $salesMissingBranch]) : null,
            $sessionsMissingBranch > 0 ? __(':count session(s) missing branch assignment.', ['count' => $sessionsMissingBranch]) : null,
            $crossBranchSales > 0 ? __(':count sale(s) outside scoped branch.', ['count' => $crossBranchSales]) : null,
        ]);

        $passed = $salesMissingBranch === 0 && $sessionsMissingBranch === 0 && $crossBranchSales === 0;

        return $this->domainResult(
            key: 'branch_compliance',
            label: __('Branch Compliance'),
            passed: $passed,
            metrics: [
                __('Sales missing branch') => $salesMissingBranch,
                __('Sessions missing branch') => $sessionsMissingBranch,
                __('Cross-branch sales') => $crossBranchSales,
            ],
            issues: array_values($issues),
        );
    }

    /**
     * @param  array<string, int>  $metrics
     * @param  list<string>  $issues
     * @return array<string, mixed>
     */
    protected function domainResult(string $key, string $label, bool $passed, array $metrics, array $issues): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'passed' => $passed,
            'verdict' => $passed ? 'PASS' : 'FAIL',
            'metrics' => $metrics,
            'issues' => $issues,
        ];
    }

    protected function salesQuery(PosCertificationScope $scope): Builder
    {
        return $this->applySaleScope(PosSale::query(), $scope);
    }

    protected function applySaleScope(Builder $query, PosCertificationScope $scope): Builder
    {
        return $query
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn (Builder $q) => $q->where('branch_id', $scope->branchId))
            ->whereBetween('sale_date', [$scope->fromDate->toDateString(), $scope->toDate->toDateString()]);
    }

    protected function returnsQuery(PosCertificationScope $scope): Builder
    {
        return PosReturn::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn (Builder $q) => $q->where('branch_id', $scope->branchId))
            ->whereBetween(DB::raw('DATE(created_at)'), [$scope->fromDate->toDateString(), $scope->toDate->toDateString()]);
    }

    protected function sessionQuery(PosCertificationScope $scope): Builder
    {
        return PosSession::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn (Builder $q) => $q->where('branch_id', $scope->branchId));
    }

    protected function reconciliationQuery(PosCertificationScope $scope): Builder
    {
        return PosCashReconciliation::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn (Builder $q) => $q->where('branch_id', $scope->branchId))
            ->whereBetween(DB::raw('DATE(created_at)'), [$scope->fromDate->toDateString(), $scope->toDate->toDateString()]);
    }
}

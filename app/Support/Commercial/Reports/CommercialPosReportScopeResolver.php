<?php

namespace App\Support\Commercial\Reports;

use App\Enums\PosPaymentMethod;
use App\Enums\PosSaleStatus;
use App\Models\Branch;
use App\Models\Pos\PosSale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CommercialPosReportScopeResolver
{
    public function __construct(
        protected CommercialPosReportReadiness $readiness,
    ) {}

    /**
     * @return array{
     *     scope: CommercialPosReportScope,
     *     branches: Collection<int, Branch>,
     *     cashiers: Collection<int, User>,
     *     can_export: bool,
     *     filters: array<string, mixed>,
     *     readiness: list<array<string, mixed>>,
     *     report_ready: bool
     * }
     */
    public function resolve(Request $request): array
    {
        $companyId = tenant()->companyId() ?? $request->user()?->company_id;

        if (! $companyId) {
            abort(403, __('Company context is required.'));
        }

        $user = $request->user();
        $canViewAllBranches = $user?->can('commercial.pos.sessions.admin') ?? false;

        $branchId = null;
        if ($request->has('branch_id')) {
            $branchId = $request->input('branch_id') !== '' ? (int) $request->input('branch_id') : null;
        } elseif (! $canViewAllBranches) {
            $branchId = tenant()->branchId();
        }

        $paymentMethod = $request->filled('payment_method') ? (string) $request->input('payment_method') : null;
        if ($paymentMethod !== null && ! in_array($paymentMethod, array_column(PosPaymentMethod::cases(), 'value'), true)) {
            $paymentMethod = null;
        }

        $status = $request->filled('status') ? (string) $request->input('status') : null;
        if ($status !== null && ! in_array($status, array_column(PosSaleStatus::cases(), 'value'), true)) {
            $status = null;
        }

        $scope = new CommercialPosReportScope(
            companyId: (int) $companyId,
            branchId: $branchId,
            fromDate: (string) $request->input('from_date', now()->startOfMonth()->toDateString()),
            toDate: (string) $request->input('to_date', now()->toDateString()),
            cashierId: $request->filled('cashier_id') ? (int) $request->input('cashier_id') : null,
            paymentMethod: $paymentMethod,
            status: $status,
            search: trim((string) $request->input('search', '')),
            tab: (string) $request->input('tab', 'sales_by_cashier'),
            page: max(1, (int) $request->input('page', 1)),
        );

        $branches = Branch::query()
            ->where('company_id', $scope->companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $cashierIds = PosSale::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->distinct()
            ->pluck('cashier_id');

        $cashiers = $cashierIds->isEmpty()
            ? collect()
            : User::query()
                ->where('company_id', $scope->companyId)
                ->whereIn('id', $cashierIds)
                ->orderBy('name')
                ->get(['id', 'name']);

        return [
            'scope' => $scope,
            'branches' => $branches,
            'cashiers' => $cashiers,
            'can_export' => $user?->can('commercial.pos.reports.export') ?? false,
            'filters' => [
                'from_date' => $scope->fromDate,
                'to_date' => $scope->toDate,
                'branch_id' => $scope->branchId,
                'cashier_id' => $scope->cashierId,
                'payment_method' => $scope->paymentMethod,
                'status' => $scope->status,
                'search' => $scope->search,
                'tab' => $scope->tab,
                'page' => $scope->page,
            ],
            'readiness' => $this->readiness->assess(),
            'report_ready' => $this->readiness->isReady(),
        ];
    }
}

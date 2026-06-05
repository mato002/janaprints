<?php

namespace App\Support\Commercial\Reports;

use App\Models\Branch;
use App\Models\Crm\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CommercialSalesReportScopeResolver
{
    public function __construct(
        protected CommercialSalesReportReadiness $readiness,
    ) {}

    /**
     * @return array{
     *     scope: CommercialSalesReportScope,
     *     branches: Collection<int, Branch>,
     *     customers: Collection<int, Customer>,
     *     salespersons: Collection<int, User>,
     *     can_export: bool,
     *     can_manage: bool,
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

        $branchId = null;
        if ($request->has('branch_id')) {
            $branchId = $request->input('branch_id') !== '' ? (int) $request->input('branch_id') : null;
        } else {
            $branchId = tenant()->branchId();
        }

        $scope = new CommercialSalesReportScope(
            companyId: (int) $companyId,
            branchId: $branchId,
            fromDate: (string) $request->input('from_date', now()->startOfMonth()->toDateString()),
            toDate: (string) $request->input('to_date', now()->toDateString()),
            customerId: $request->filled('customer_id') ? (int) $request->input('customer_id') : null,
            salespersonId: $request->filled('salesperson_id') ? (int) $request->input('salesperson_id') : null,
            status: $request->filled('status') ? (string) $request->input('status') : null,
            search: trim((string) $request->input('search', '')),
            tab: (string) $request->input('tab', 'summary'),
            topLimit: in_array((int) $request->input('top_limit', 10), [10, 25, 50], true)
                ? (int) $request->input('top_limit', 10)
                : 10,
            topBy: in_array((string) $request->input('top_by', 'revenue'), ['revenue', 'orders', 'lifetime'], true)
                ? (string) $request->input('top_by', 'revenue')
                : 'revenue',
            page: max(1, (int) $request->input('page', 1)),
        );

        $branches = Branch::query()
            ->where('company_id', $scope->companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $customers = Customer::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->orderBy('company_name')
            ->limit(500)
            ->get(['id', 'company_name']);

        $salespersonIds = SalesOrder::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->distinct()
            ->pluck('created_by');

        $salespersons = $salespersonIds->isEmpty()
            ? collect()
            : User::query()
                ->where('company_id', $scope->companyId)
                ->where('is_active', true)
                ->whereIn('id', $salespersonIds)
                ->orderBy('name')
                ->get(['id', 'name']);

        $user = $request->user();

        return [
            'scope' => $scope,
            'branches' => $branches,
            'customers' => $customers,
            'salespersons' => $salespersons,
            'can_export' => $user?->can('commercial.reports.export') ?? false,
            'can_manage' => $user?->can('commercial.reports.sales.manage') ?? false,
            'filters' => [
                'from_date' => $scope->fromDate,
                'to_date' => $scope->toDate,
                'branch_id' => $scope->branchId,
                'customer_id' => $scope->customerId,
                'salesperson_id' => $scope->salespersonId,
                'status' => $scope->status,
                'search' => $scope->search,
                'tab' => $scope->tab,
                'top_limit' => $scope->topLimit,
                'top_by' => $scope->topBy,
                'page' => $scope->page,
            ],
            'readiness' => $this->readiness->assess(),
            'report_ready' => $this->readiness->isReady(),
        ];
    }
}

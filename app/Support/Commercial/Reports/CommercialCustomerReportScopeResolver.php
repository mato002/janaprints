<?php

namespace App\Support\Commercial\Reports;

use App\Models\Branch;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CommercialCustomerReportScopeResolver
{
    public function __construct(
        protected CommercialCustomerReportReadiness $readiness,
    ) {}

    /**
     * @return array{
     *     scope: CommercialCustomerReportScope,
     *     branches: Collection<int, Branch>,
     *     salespersons: Collection<int, User>,
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

        $branchId = null;
        if ($request->has('branch_id')) {
            $branchId = $request->input('branch_id') !== '' ? (int) $request->input('branch_id') : null;
        } else {
            $branchId = tenant()->branchId();
        }

        $activityStatus = $request->filled('activity_status') ? (string) $request->input('activity_status') : null;
        if ($activityStatus !== null && ! in_array($activityStatus, ['active', 'inactive', 'new', 'dormant'], true)) {
            $activityStatus = null;
        }

        $scope = new CommercialCustomerReportScope(
            companyId: (int) $companyId,
            branchId: $branchId,
            fromDate: (string) $request->input('from_date', CommercialReportDateDefaults::defaultFromDate()),
            toDate: (string) $request->input('to_date', CommercialReportDateDefaults::defaultToDate()),
            customerType: $request->filled('customer_type') ? (string) $request->input('customer_type') : null,
            status: $request->filled('status') ? (string) $request->input('status') : null,
            salespersonId: $request->filled('salesperson_id') ? (int) $request->input('salesperson_id') : null,
            activityStatus: $activityStatus,
            search: trim((string) $request->input('search', '')),
            tab: (string) $request->input('tab', 'summary'),
            topLimit: in_array((int) $request->input('top_limit', 10), [10, 25, 50], true)
                ? (int) $request->input('top_limit', 10)
                : 10,
            page: max(1, (int) $request->input('page', 1)),
        );

        $branches = Branch::query()
            ->where('company_id', $scope->companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

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
            'salespersons' => $salespersons,
            'can_export' => $user?->can('commercial.reports.customers.export') ?? false,
            'filters' => [
                'from_date' => $scope->fromDate,
                'to_date' => $scope->toDate,
                'branch_id' => $scope->branchId,
                'customer_type' => $scope->customerType,
                'status' => $scope->status,
                'salesperson_id' => $scope->salespersonId,
                'activity_status' => $scope->activityStatus,
                'search' => $scope->search,
                'tab' => $scope->tab,
                'top_limit' => $scope->topLimit,
                'page' => $scope->page,
            ],
            'readiness' => $this->readiness->assess(),
            'report_ready' => $this->readiness->isReady(),
        ];
    }
}

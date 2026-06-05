<?php

namespace App\Support\Production\Reports;

use App\Enums\ProductionType;
use App\Models\Branch;
use App\Models\Crm\Customer;
use App\Models\Production\JobCostSheet;
use App\Models\Production\ProductionJobCard;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CostingReportScopeResolver
{
    public function __construct(
        protected CostingReportReadiness $readiness,
    ) {}

    /**
     * @return array{
     *     scope: CostingReportScope,
     *     branches: Collection<int, Branch>,
     *     customers: Collection<int, Customer>,
     *     job_cards: Collection<int, ProductionJobCard>,
     *     production_types: list<ProductionType>,
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

        $scope = new CostingReportScope(
            companyId: (int) $companyId,
            branchId: $branchId,
            fromDate: (string) $request->input('from_date', now()->startOfMonth()->toDateString()),
            toDate: (string) $request->input('to_date', now()->toDateString()),
            customerId: $request->filled('customer_id') ? (int) $request->input('customer_id') : null,
            productionType: $request->filled('production_type') ? (string) $request->input('production_type') : null,
            jobCardId: $request->filled('job_card_id') ? (int) $request->input('job_card_id') : null,
            search: trim((string) $request->input('search', '')),
            tab: (string) $request->input('tab', 'job_profitability'),
            page: max(1, (int) $request->input('page', 1)),
        );

        $branches = Branch::query()
            ->where('company_id', $scope->companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $sheetQuery = JobCostSheet::query()->where('job_cost_sheets.company_id', $scope->companyId);
        if ($scope->branchId) {
            $sheetQuery->where('job_cost_sheets.branch_id', $scope->branchId);
        }

        $customerIds = (clone $sheetQuery)
            ->join('production_job_cards', 'production_job_cards.id', '=', 'job_cost_sheets.production_job_card_id')
            ->distinct()
            ->pluck('production_job_cards.customer_id')
            ->filter();

        $customers = Customer::query()
            ->where('company_id', $scope->companyId)
            ->whereIn('id', $customerIds)
            ->orderBy('company_name')
            ->limit(200)
            ->get(['id', 'company_name']);

        $jobCards = ProductionJobCard::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->orderByDesc('created_at')
            ->limit(200)
            ->get(['id', 'job_card_number']);

        $user = $request->user();

        return [
            'scope' => $scope,
            'branches' => $branches,
            'customers' => $customers,
            'job_cards' => $jobCards,
            'production_types' => ProductionType::cases(),
            'can_export' => $user?->can('reports.costing.export') ?? false,
            'filters' => [
                'from_date' => $scope->fromDate,
                'to_date' => $scope->toDate,
                'branch_id' => $scope->branchId,
                'customer_id' => $scope->customerId,
                'production_type' => $scope->productionType,
                'job_card_id' => $scope->jobCardId,
                'search' => $scope->search,
                'tab' => $scope->tab,
                'page' => $scope->page,
            ],
            'readiness' => $this->readiness->assess(),
            'report_ready' => $this->readiness->isReady(),
        ];
    }
}

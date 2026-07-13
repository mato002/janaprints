<?php

namespace App\Support\Commercial\Reports;

use App\Models\Branch;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadSource;
use App\Models\Sales\Quotation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CommercialConversionReportScopeResolver
{
    public function __construct(
        protected CommercialConversionReportReadiness $readiness,
    ) {}

    /**
     * @return array{
     *     scope: CommercialConversionReportScope,
     *     branches: Collection<int, Branch>,
     *     lead_sources: Collection<int, LeadSource>,
     *     salespersons: Collection<int, User>,
     *     can_export: bool,
     *     filters: array<string, mixed>,
     *     readiness: list<array<string, mixed>>,
     *     report_ready: bool,
     *     has_production_pipeline: bool,
     *     has_dispatch_pipeline: bool
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

        $scope = new CommercialConversionReportScope(
            companyId: (int) $companyId,
            branchId: $branchId,
            fromDate: (string) $request->input('from_date', CommercialReportDateDefaults::defaultFromDate()),
            toDate: (string) $request->input('to_date', CommercialReportDateDefaults::defaultToDate()),
            salespersonId: $request->filled('salesperson_id') ? (int) $request->input('salesperson_id') : null,
            leadSourceId: $request->filled('lead_source_id') ? (int) $request->input('lead_source_id') : null,
            customerType: $request->filled('customer_type') ? (string) $request->input('customer_type') : null,
            status: $request->filled('status') ? (string) $request->input('status') : null,
            search: trim((string) $request->input('search', '')),
            tab: (string) $request->input('tab', 'full_funnel'),
        );

        $branches = Branch::query()
            ->where('company_id', $scope->companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $leadSources = LeadSource::query()
            ->where('company_id', $scope->companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $salespersonIds = collect()
            ->merge(
                Lead::query()
                    ->where('company_id', $scope->companyId)
                    ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
                    ->whereNotNull('assigned_to')
                    ->distinct()
                    ->pluck('assigned_to'),
            )
            ->merge(
                Quotation::query()
                    ->where('company_id', $scope->companyId)
                    ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
                    ->distinct()
                    ->pluck('prepared_by'),
            )
            ->unique()
            ->filter();

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
            'lead_sources' => $leadSources,
            'salespersons' => $salespersons,
            'can_export' => $user?->can('commercial.reports.conversion.export') ?? false,
            'filters' => [
                'from_date' => $scope->fromDate,
                'to_date' => $scope->toDate,
                'branch_id' => $scope->branchId,
                'salesperson_id' => $scope->salespersonId,
                'lead_source_id' => $scope->leadSourceId,
                'customer_type' => $scope->customerType,
                'status' => $scope->status,
                'search' => $scope->search,
                'tab' => $scope->tab,
            ],
            'readiness' => $this->readiness->assess(),
            'report_ready' => $this->readiness->isReady(),
            'has_production_pipeline' => $this->readiness->hasProductionPipeline(),
            'has_dispatch_pipeline' => $this->readiness->hasDispatchPipeline(),
        ];
    }
}

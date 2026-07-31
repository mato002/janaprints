<?php

namespace App\Http\Controllers\Admin\Production;

use App\Http\Controllers\Controller;
use App\Support\Commercial\Reports\CommercialReportExportService;
use App\Support\Production\Reports\CostingReportPresenter;
use App\Support\Production\Reports\CostingReportScope;
use App\Support\Production\Reports\CostingReportScopeResolver;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class CostingReportController extends Controller
{
    public function __construct(
        protected CostingReportPresenter $presenter,
        protected CostingReportScopeResolver $scopeResolver,
        protected CommercialReportExportService $exportService,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('reports.costing.view'), 403);

        return view('admin.production.reports.index', $this->presenter->present($request));
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->can('reports.costing.export'), 403);

        $format = $request->input('format', 'csv');
        if (! in_array($format, ['csv', 'excel', 'pdf'], true)) {
            $format = 'csv';
        }

        $resolved = $this->scopeResolver->resolve($request);

        return $this->exportService->download(
            request: $request,
            scopePayload: $this->serializeScope($resolved['scope']),
            module: 'costing',
            tab: $resolved['scope']->tab,
            format: $format
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeScope(CostingReportScope $scope): array
    {
        return [
            'company_id' => $scope->companyId,
            'branch_id' => $scope->branchId,
            'from_date' => $scope->fromDate,
            'to_date' => $scope->toDate,
            'customer_id' => $scope->customerId,
            'production_type' => $scope->productionType,
            'job_card_id' => $scope->jobCardId,
            'search' => $scope->search,
        ];
    }
}

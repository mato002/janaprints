<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Http\Controllers\Controller;
use App\Support\Commercial\Reports\CommercialReportExportService;
use App\Support\Commercial\Reports\CommercialSalesReportPresenter;
use App\Support\Commercial\Reports\CommercialSalesReportScopeResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommercialSalesReportController extends Controller
{
    public function __construct(
        protected CommercialSalesReportPresenter $presenter,
        protected CommercialSalesReportScopeResolver $scopeResolver,
        protected CommercialReportExportService $exportService,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('commercial.reports.sales.view'), 403);

        $payload = $this->presenter->present($request);

        return view('admin.commercial.reports.sales.index', $payload);
    }

    public function export(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('commercial.reports.export'), 403);

        $format = $request->input('format', 'csv');
        if (! in_array($format, ['csv', 'excel', 'pdf'], true)) {
            $format = 'csv';
        }

        $resolved = $this->scopeResolver->resolve($request);

        return $this->exportService->queue(
            request: $request,
            scopePayload: $this->serializeScope($resolved['scope']),
            module: 'sales',
            tab: $resolved['scope']->tab,
            format: $format,
            redirectRoute: 'commercial.reports.sales.index',
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeScope(\App\Support\Commercial\Reports\CommercialSalesReportScope $scope): array
    {
        return [
            'company_id' => $scope->companyId,
            'branch_id' => $scope->branchId,
            'from_date' => $scope->fromDate,
            'to_date' => $scope->toDate,
            'customer_id' => $scope->customerId,
            'salesperson_id' => $scope->salespersonId,
            'status' => $scope->status,
            'search' => $scope->search,
            'top_limit' => $scope->topLimit,
            'top_by' => $scope->topBy,
        ];
    }
}

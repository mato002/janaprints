<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Http\Controllers\Controller;
use App\Support\Commercial\Reports\CommercialConversionReportPresenter;
use App\Support\Commercial\Reports\CommercialConversionReportScope;
use App\Support\Commercial\Reports\CommercialConversionReportScopeResolver;
use App\Support\Commercial\Reports\CommercialReportExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommercialConversionReportController extends Controller
{
    public function __construct(
        protected CommercialConversionReportPresenter $presenter,
        protected CommercialConversionReportScopeResolver $scopeResolver,
        protected CommercialReportExportService $exportService,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('commercial.reports.conversion.view'), 403);

        return view('admin.commercial.reports.conversion.index', $this->presenter->present($request));
    }

    public function export(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('commercial.reports.conversion.export'), 403);

        $format = $request->input('format', 'csv');
        if (! in_array($format, ['csv', 'excel', 'pdf'], true)) {
            $format = 'csv';
        }

        $resolved = $this->scopeResolver->resolve($request);

        return $this->exportService->queue(
            request: $request,
            scopePayload: $this->serializeScope($resolved['scope']),
            module: 'conversion',
            tab: $resolved['scope']->tab,
            format: $format,
            redirectRoute: 'admin.commercial.reports.conversion.index',
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeScope(CommercialConversionReportScope $scope): array
    {
        return [
            'company_id' => $scope->companyId,
            'branch_id' => $scope->branchId,
            'from_date' => $scope->fromDate,
            'to_date' => $scope->toDate,
            'salesperson_id' => $scope->salespersonId,
            'lead_source_id' => $scope->leadSourceId,
            'customer_type' => $scope->customerType,
            'status' => $scope->status,
            'search' => $scope->search,
        ];
    }
}

<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Http\Controllers\Controller;
use App\Support\Commercial\Reports\CommercialQuotationReportPresenter;
use App\Support\Commercial\Reports\CommercialQuotationReportScope;
use App\Support\Commercial\Reports\CommercialQuotationReportScopeResolver;
use App\Support\Commercial\Reports\CommercialReportExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class CommercialQuotationReportController extends Controller
{
    public function __construct(
        protected CommercialQuotationReportPresenter $presenter,
        protected CommercialQuotationReportScopeResolver $scopeResolver,
        protected CommercialReportExportService $exportService,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('commercial.reports.quotations.view'), 403);

        return view('admin.commercial.reports.quotations.index', $this->presenter->present($request));
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->can('commercial.reports.quotations.export'), 403);

        $format = $request->input('format', 'csv');
        if (! in_array($format, ['csv', 'excel', 'pdf'], true)) {
            $format = 'csv';
        }

        $resolved = $this->scopeResolver->resolve($request);

        return $this->exportService->download(
            request: $request,
            scopePayload: $this->serializeScope($resolved['scope']),
            module: 'quotations',
            tab: $resolved['scope']->tab,
            format: $format
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeScope(CommercialQuotationReportScope $scope): array
    {
        return [
            'company_id' => $scope->companyId,
            'branch_id' => $scope->branchId,
            'from_date' => $scope->fromDate,
            'to_date' => $scope->toDate,
            'customer_id' => $scope->customerId,
            'salesperson_id' => $scope->salespersonId,
            'status' => $scope->status,
            'expiry_status' => $scope->expiryStatus,
            'search' => $scope->search,
        ];
    }
}

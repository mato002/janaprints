<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Http\Controllers\Controller;
use App\Support\Commercial\Reports\CommercialCustomerReportPresenter;
use App\Support\Commercial\Reports\CommercialCustomerReportScope;
use App\Support\Commercial\Reports\CommercialCustomerReportScopeResolver;
use App\Support\Commercial\Reports\CommercialReportExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommercialCustomerReportController extends Controller
{
    public function __construct(
        protected CommercialCustomerReportPresenter $presenter,
        protected CommercialCustomerReportScopeResolver $scopeResolver,
        protected CommercialReportExportService $exportService,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('commercial.reports.customers.view'), 403);

        return view('admin.commercial.reports.customers.index', $this->presenter->present($request));
    }

    public function export(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('commercial.reports.customers.export'), 403);

        $format = $request->input('format', 'csv');
        if (! in_array($format, ['csv', 'excel', 'pdf'], true)) {
            $format = 'csv';
        }

        $resolved = $this->scopeResolver->resolve($request);

        return $this->exportService->queue(
            request: $request,
            scopePayload: $this->serializeScope($resolved['scope']),
            module: 'customers',
            tab: $resolved['scope']->tab,
            format: $format,
            redirectRoute: 'admin.commercial.reports.customers.index',
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeScope(CommercialCustomerReportScope $scope): array
    {
        return [
            'company_id' => $scope->companyId,
            'branch_id' => $scope->branchId,
            'from_date' => $scope->fromDate,
            'to_date' => $scope->toDate,
            'customer_type' => $scope->customerType,
            'status' => $scope->status,
            'salesperson_id' => $scope->salespersonId,
            'activity_status' => $scope->activityStatus,
            'search' => $scope->search,
            'top_limit' => $scope->topLimit,
        ];
    }
}

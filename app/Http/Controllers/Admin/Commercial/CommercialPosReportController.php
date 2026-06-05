<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Http\Controllers\Controller;
use App\Support\Commercial\Reports\CommercialPosReportPresenter;
use App\Support\Commercial\Reports\CommercialPosReportScope;
use App\Support\Commercial\Reports\CommercialPosReportScopeResolver;
use App\Support\Commercial\Reports\CommercialReportExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommercialPosReportController extends Controller
{
    public function __construct(
        protected CommercialPosReportPresenter $presenter,
        protected CommercialPosReportScopeResolver $scopeResolver,
        protected CommercialReportExportService $exportService,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('commercial.pos.reports.view'), 403);

        $payload = $this->presenter->present($request);

        return view('admin.commercial.pos.intelligence.index', $payload);
    }

    public function export(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('commercial.pos.reports.export'), 403);

        $format = $request->input('format', 'csv');
        if (! in_array($format, ['csv', 'excel', 'pdf'], true)) {
            $format = 'csv';
        }

        $resolved = $this->scopeResolver->resolve($request);

        return $this->exportService->queue(
            request: $request,
            scopePayload: $this->serializeScope($resolved['scope']),
            module: 'pos',
            tab: $resolved['scope']->tab,
            format: $format,
            redirectRoute: 'commercial.pos.reports.index',
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeScope(CommercialPosReportScope $scope): array
    {
        return [
            'company_id' => $scope->companyId,
            'branch_id' => $scope->branchId,
            'from_date' => $scope->fromDate,
            'to_date' => $scope->toDate,
            'cashier_id' => $scope->cashierId,
            'payment_method' => $scope->paymentMethod,
            'status' => $scope->status,
            'search' => $scope->search,
        ];
    }
}

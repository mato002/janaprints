<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Http\Controllers\Controller;
use App\Support\Commercial\Reports\CommercialArtworkReportPresenter;
use App\Support\Commercial\Reports\CommercialArtworkReportScopeResolver;
use App\Support\Commercial\Reports\CommercialReportExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class CommercialArtworkReportController extends Controller
{
    public function __construct(
        protected CommercialArtworkReportPresenter $presenter,
        protected CommercialArtworkReportScopeResolver $scopeResolver,
        protected CommercialReportExportService $exportService,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('commercial.reports.artwork.view'), 403);

        $payload = $this->presenter->present($request);

        return view('admin.commercial.reports.artwork.index', $payload);
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->can('commercial.reports.artwork.export'), 403);

        $format = $request->input('format', 'csv');
        if (! in_array($format, ['csv', 'excel', 'pdf'], true)) {
            $format = 'csv';
        }

        $resolved = $this->scopeResolver->resolve($request);

        return $this->exportService->download(
            request: $request,
            scopePayload: $this->serializeScope($resolved['scope']),
            module: 'artwork',
            tab: $resolved['scope']->tab,
            format: $format
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeScope(\App\Support\Commercial\Reports\CommercialArtworkReportScope $scope): array
    {
        return [
            'company_id' => $scope->companyId,
            'branch_id' => $scope->branchId,
            'from_date' => $scope->fromDate,
            'to_date' => $scope->toDate,
            'customer_id' => $scope->customerId,
            'designer_id' => $scope->designerId,
            'status' => $scope->status,
            'approval_status' => $scope->approvalStatus,
            'delay_status' => $scope->delayStatus,
            'search' => $scope->search,
        ];
    }
}

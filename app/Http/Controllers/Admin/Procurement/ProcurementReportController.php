<?php

namespace App\Http\Controllers\Admin\Procurement;

use App\Http\Controllers\Controller;
use App\Support\Commercial\Reports\CommercialReportExportService;
use App\Support\Procurement\Reports\ProcurementReportPresenter;
use App\Support\Procurement\Reports\ProcurementReportScope;
use App\Support\Procurement\Reports\ProcurementReportScopeResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProcurementReportController extends Controller
{
    public function __construct(
        protected ProcurementReportPresenter $presenter,
        protected ProcurementReportScopeResolver $scopeResolver,
        protected CommercialReportExportService $exportService,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('reports.procurement.view'), 403);

        return view('admin.procurement.reports.index', $this->presenter->present($request));
    }

    public function export(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('reports.procurement.export'), 403);

        $format = $request->input('format', 'csv');
        if (! in_array($format, ['csv', 'excel', 'pdf'], true)) {
            $format = 'csv';
        }

        $resolved = $this->scopeResolver->resolve($request);

        return $this->exportService->queue(
            request: $request,
            scopePayload: $this->serializeScope($resolved['scope']),
            module: 'procurement',
            tab: $resolved['scope']->tab,
            format: $format,
            redirectRoute: 'admin.procurement.reports.index',
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeScope(ProcurementReportScope $scope): array
    {
        return [
            'company_id' => $scope->companyId,
            'branch_id' => $scope->branchId,
            'from_date' => $scope->fromDate,
            'to_date' => $scope->toDate,
            'supplier_id' => $scope->supplierId,
            'warehouse_id' => $scope->warehouseId,
            'category_id' => $scope->categoryId,
            'search' => $scope->search,
            'top_limit' => $scope->topLimit,
        ];
    }
}

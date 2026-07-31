<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Support\Commercial\Reports\CommercialReportExportService;
use App\Support\Inventory\Reports\InventoryReportPresenter;
use App\Support\Inventory\Reports\InventoryReportScope;
use App\Support\Inventory\Reports\InventoryReportScopeResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryReportController extends Controller
{
    public function __construct(
        protected InventoryReportPresenter $presenter,
        protected InventoryReportScopeResolver $scopeResolver,
        protected CommercialReportExportService $exportService,
    ) {}

    public function index(Request $request): View
    {
        abort_unless(
            $request->user()?->can('reports.inventory.view') || $request->user()?->can('reports.view'),
            403
        );

        return view('admin.inventory.reports.index', $this->presenter->present($request));
    }

    public function export(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('reports.inventory.export'), 403);

        $format = $request->input('format', 'csv');
        if (! in_array($format, ['csv', 'excel', 'pdf'], true)) {
            $format = 'csv';
        }

        $resolved = $this->scopeResolver->resolve($request);

        return $this->exportService->queue(
            request: $request,
            scopePayload: $this->serializeScope($resolved['scope']),
            module: 'inventory',
            tab: $resolved['scope']->tab,
            format: $format,
            redirectRoute: 'admin.inventory.reports.index',
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeScope(InventoryReportScope $scope): array
    {
        return [
            'company_id' => $scope->companyId,
            'branch_id' => $scope->branchId,
            'from_date' => $scope->fromDate,
            'to_date' => $scope->toDate,
            'warehouse_id' => $scope->warehouseId,
            'category_id' => $scope->categoryId,
            'subcategory_id' => $scope->subcategoryId,
            'supplier_id' => $scope->supplierId,
            'item_id' => $scope->itemId,
            'search' => $scope->search,
        ];
    }
}

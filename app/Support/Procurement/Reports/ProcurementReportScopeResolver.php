<?php

namespace App\Support\Procurement\Reports;

use App\Models\Branch;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\Warehouse;
use App\Models\Procurement\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProcurementReportScopeResolver
{
    public function __construct(
        protected ProcurementReportReadiness $readiness,
    ) {}

    /**
     * @return array{
     *     scope: ProcurementReportScope,
     *     branches: Collection<int, Branch>,
     *     warehouses: Collection<int, Warehouse>,
     *     categories: Collection<int, InventoryCategory>,
     *     suppliers: Collection<int, Vendor>,
     *     can_export: bool,
     *     filters: array<string, mixed>,
     *     readiness: list<array<string, mixed>>,
     *     report_ready: bool
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

        $scope = new ProcurementReportScope(
            companyId: (int) $companyId,
            branchId: $branchId,
            fromDate: (string) $request->input('from_date', now()->startOfMonth()->toDateString()),
            toDate: (string) $request->input('to_date', now()->toDateString()),
            supplierId: $request->filled('supplier_id') ? (int) $request->input('supplier_id') : null,
            warehouseId: $request->filled('warehouse_id') ? (int) $request->input('warehouse_id') : null,
            categoryId: $request->filled('category_id') ? (int) $request->input('category_id') : null,
            search: trim((string) $request->input('search', '')),
            tab: (string) $request->input('tab', 'summary'),
            topLimit: in_array((int) $request->input('top_limit', 10), [10, 25, 50], true)
                ? (int) $request->input('top_limit', 10)
                : 10,
            page: max(1, (int) $request->input('page', 1)),
        );

        $branches = Branch::query()
            ->where('company_id', $scope->companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $warehouses = Warehouse::query()
            ->where('company_id', $scope->companyId)
            ->where('is_active', true)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->orderBy('name')
            ->get(['id', 'name']);

        $categories = InventoryCategory::query()
            ->where('company_id', $scope->companyId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $suppliers = Vendor::query()
            ->where('company_id', $scope->companyId)
            ->orderBy('vendor_name')
            ->limit(500)
            ->get(['id', 'vendor_name']);

        $user = $request->user();

        return [
            'scope' => $scope,
            'branches' => $branches,
            'warehouses' => $warehouses,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'can_export' => $user?->can('reports.procurement.export') ?? false,
            'filters' => [
                'from_date' => $scope->fromDate,
                'to_date' => $scope->toDate,
                'branch_id' => $scope->branchId,
                'supplier_id' => $scope->supplierId,
                'warehouse_id' => $scope->warehouseId,
                'category_id' => $scope->categoryId,
                'search' => $scope->search,
                'tab' => $scope->tab,
                'top_limit' => $scope->topLimit,
                'page' => $scope->page,
            ],
            'readiness' => $this->readiness->assess(),
            'report_ready' => $this->readiness->isReady(),
        ];
    }
}

<?php

namespace App\Support\Reports;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class IntelligenceScopeResolver
{
    /**
     * @return array{scope: IntelligenceScope, branches: Collection<int, Branch>, can_export: bool}
     */
    public function resolve(
        Request $request,
        bool $includeWarehouse = false,
        bool $includeVendor = false,
        bool $includeCustomer = false,
        bool $defaultBranchFromTenant = true,
    ): array {
        $companyId = tenant()->companyId() ?? $request->user()?->company_id;

        if (! $companyId) {
            abort(403, __('Company context is required.'));
        }

        $branchId = null;
        if ($request->has('branch_id')) {
            $branchId = $request->input('branch_id') !== '' ? (int) $request->input('branch_id') : null;
        } elseif ($defaultBranchFromTenant) {
            $branchId = tenant()->branchId();
        }

        $scope = new IntelligenceScope(
            companyId: (int) $companyId,
            branchId: $branchId,
            fromDate: $request->input('from_date', now()->startOfMonth()->toDateString()),
            toDate: $request->input('to_date', now()->toDateString()),
            warehouseId: $includeWarehouse && $request->filled('warehouse_id')
                ? (int) $request->input('warehouse_id')
                : null,
            categoryId: $request->filled('category_id') ? (int) $request->input('category_id') : null,
            vendorId: $includeVendor && $request->filled('vendor_id') ? (int) $request->input('vendor_id') : null,
            status: $request->filled('status') ? (string) $request->input('status') : null,
            customerId: $includeCustomer && $request->filled('customer_id') ? (int) $request->input('customer_id') : null,
            kpiCategory: $request->filled('kpi_category') ? (string) $request->input('kpi_category') : null,
        );

        $branches = Branch::query()
            ->where('company_id', $scope->companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return [
            'scope' => $scope,
            'branches' => $branches,
            'can_export' => $request->user()?->can('reports.export') ?? false,
            'filters' => [
                'from_date' => $scope->fromDate,
                'to_date' => $scope->toDate,
                'branch_id' => $scope->branchId,
                'warehouse_id' => $scope->warehouseId,
                'category_id' => $scope->categoryId,
                'vendor_id' => $scope->vendorId,
                'status' => $scope->status,
                'customer_id' => $scope->customerId,
                'kpi_category' => $scope->kpiCategory,
            ],
        ];
    }
}

<?php

namespace App\Http\Controllers\Admin\Inventory\Concerns;

use App\Http\Controllers\Admin\Concerns\ResolvesEntityCode;

trait ResolvesInventoryTenant
{
    use ResolvesEntityCode;
    /**
     * @return array{companyId: int, branchId: int}
     */
    protected function tenantIds(): array
    {
        $companyId = tenant()->companyId() ?? auth()->user()->company_id;
        $branchId = tenant()->branchId() ?? auth()->user()->default_branch_id;

        return ['companyId' => $companyId, 'branchId' => $branchId];
    }
}

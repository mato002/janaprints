<?php

namespace App\Http\Controllers\Admin\Crm\Concerns;

use App\Enums\DocumentType;
use App\Support\Platform\NumberingService;
use Illuminate\Http\Request;

trait ResolvesCrmTenant
{
    protected function tenantIds(Request $request): array
    {
        $companyId = auth()->user()->hasRole('Super Admin')
            ? (int) ($request->input('company_id') ?: tenant()->companyId())
            : (int) auth()->user()->company_id;

        $branchId = auth()->user()->hasRole('Super Admin')
            ? (int) ($request->input('branch_id') ?: tenant()->branchId())
            : (int) (tenant()->branchId() ?: auth()->user()->default_branch_id);

        return compact('companyId', 'branchId');
    }

    protected function nextCustomerCode(int $companyId): string
    {
        $branchId = tenant()->branchId() ?? auth()->user()?->default_branch_id;

        return app(NumberingService::class)->next(
            DocumentType::Customer,
            $companyId,
            $branchId ? (int) $branchId : null,
        );
    }
}

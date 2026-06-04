<?php

namespace App\Http\Controllers\Admin\Procurement\Concerns;

use App\Enums\DocumentType;
use App\Support\Platform\NumberingService;
use Illuminate\Http\Request;

trait ResolvesProcurementTenant
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

    protected function nextNumber(DocumentType $type, int $companyId, ?int $branchId = null): string
    {
        $branchId ??= tenant()->branchId() ?? auth()->user()?->default_branch_id;

        return app(NumberingService::class)->next(
            $type,
            $companyId,
            $branchId ? (int) $branchId : null,
        );
    }
}

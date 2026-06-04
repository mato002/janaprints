<?php

namespace App\Http\Controllers\Admin\Accounting\Concerns;

trait ResolvesAccountingTenant
{
    /**
     * @return array{companyId: int, branchId: int|null}
     */
    protected function tenantIds(): array
    {
        $companyId = tenant()->companyId() ?? auth()->user()?->company_id;

        if (! $companyId) {
            abort(403, __('Company context is required.'));
        }

        return [
            'companyId' => (int) $companyId,
            'branchId' => tenant()->branchId(),
        ];
    }
}

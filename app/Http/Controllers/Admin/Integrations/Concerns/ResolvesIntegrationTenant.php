<?php

namespace App\Http\Controllers\Admin\Integrations\Concerns;

use Illuminate\Http\Request;

trait ResolvesIntegrationTenant
{
    /**
     * @return array{companyId: int}
     */
    protected function tenantIds(Request $request): array
    {
        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return ['companyId' => (int) $companyId];
    }
}

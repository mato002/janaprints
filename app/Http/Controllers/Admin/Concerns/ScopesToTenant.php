<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;

trait ScopesToTenant
{
    protected function scopeToTenant(Builder $query, ?string $companyColumn = null): Builder
    {
        $companyColumn ??= $query->getModel() instanceof Company ? 'id' : 'company_id';
        if (method_exists($query->getModel(), 'scopeForTenant')) {
            return $query->forTenant();
        }

        if (tenant()->isSuperAdmin && ! tenant()->hasCompany()) {
            return $query;
        }

        if ($companyId = tenant()->companyId()) {
            return $query->where($companyColumn, $companyId);
        }

        return $query->whereRaw('1 = 0');
    }
}

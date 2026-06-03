<?php

namespace App\Models\Concerns;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeForTenant(Builder $query): Builder
    {
        if (tenant()->isSuperAdmin && ! tenant()->hasCompany()) {
            return $query;
        }

        if ($companyId = tenant()->companyId()) {
            $query->where($this->getTable().'.company_id', $companyId);
        } else {
            return $query->whereRaw('1 = 0');
        }

        if (
            property_exists($this, 'tenantScopedToBranch')
            && $this->tenantScopedToBranch
            && tenant()->branchId()
        ) {
            $query->where($this->getTable().'.branch_id', tenant()->branchId());
        }

        return $query;
    }
}

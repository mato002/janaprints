<?php

namespace App\Policies\PrintingIntelligence;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait EnsuresCompanyTenant
{
    protected function sameCompany(User $user, Model $model): bool
    {
        if (! isset($model->company_id)) {
            return false;
        }

        $tenantCompanyId = tenant()->companyId() ?? $user->company_id;

        return (int) $model->company_id === (int) $tenantCompanyId;
    }
}

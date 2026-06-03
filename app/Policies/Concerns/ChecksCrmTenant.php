<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait ChecksCrmTenant
{
    protected function sameCompany(User $user, int $companyId): bool
    {
        return $user->hasRole('Super Admin') || $user->company_id === $companyId;
    }

    protected function sameTenant(User $user, object $model): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($user->company_id !== $model->company_id) {
            return false;
        }

        if (
            property_exists($model, 'branch_id')
            && $model->branch_id
            && tenant()->branchId()
            && $model->branch_id !== tenant()->branchId()
        ) {
            return false;
        }

        return true;
    }
}

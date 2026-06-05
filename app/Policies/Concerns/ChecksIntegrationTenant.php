<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait ChecksIntegrationTenant
{
    protected function sameTenant(User $user, object $model): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->company_id === $model->company_id;
    }
}

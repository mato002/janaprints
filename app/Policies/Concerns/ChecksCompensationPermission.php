<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait ChecksCompensationPermission
{
    protected function sameCompany(User $user, int $companyId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->company_id === $companyId;
    }
}

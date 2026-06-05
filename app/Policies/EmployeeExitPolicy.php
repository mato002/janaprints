<?php

namespace App\Policies;

use App\Models\Hr\EmployeeExit;
use App\Models\User;

class EmployeeExitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('hr.exit.view');
    }

    public function view(User $user, EmployeeExit $exit): bool
    {
        return $user->can('hr.exit.view') && $this->sameCompany($user, $exit->company_id);
    }

    public function create(User $user): bool
    {
        return $user->can('hr.exit.manage');
    }

    public function update(User $user, EmployeeExit $exit): bool
    {
        return $user->can('hr.exit.manage') && $this->sameCompany($user, $exit->company_id);
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->company_id === $companyId;
    }
}

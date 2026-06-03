<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('departments.manage');
    }

    public function view(User $user, Department $department): bool
    {
        return $user->can('departments.manage') && $this->sameCompany($user, $department->company_id);
    }

    public function create(User $user): bool
    {
        return $user->can('departments.manage');
    }

    public function update(User $user, Department $department): bool
    {
        return $user->can('departments.manage') && $this->sameCompany($user, $department->company_id);
    }

    public function delete(User $user, Department $department): bool
    {
        return $user->can('departments.manage') && $this->sameCompany($user, $department->company_id);
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->company_id === $companyId;
    }
}

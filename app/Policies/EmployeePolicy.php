<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('employees.manage');
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->can('employees.manage') && $this->sameCompany($user, $employee->company_id);
    }

    public function create(User $user): bool
    {
        return $user->can('employees.manage');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->can('employees.manage') && $this->sameCompany($user, $employee->company_id);
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->can('employees.manage') && $this->sameCompany($user, $employee->company_id);
    }

    public function email(User $user): bool
    {
        return $user->can('employees.email.send');
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->company_id === $companyId;
    }
}

<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class Employee360Policy
{
    public function viewAny(User $user): bool
    {
        return $user->can('hr.employee360.view');
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->can('hr.employee360.view') && $this->sameCompany($user, $employee->company_id);
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->can('hr.employee360.edit') && $this->sameCompany($user, $employee->company_id);
    }

    public function audit(User $user): bool
    {
        return $user->can('hr.employee360.audit');
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->company_id === $companyId;
    }
}

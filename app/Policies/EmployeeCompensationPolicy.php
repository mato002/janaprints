<?php

namespace App\Policies;

use App\Models\Hr\EmployeeCompensation;
use App\Models\User;
use App\Policies\Concerns\ChecksCompensationPermission;

class EmployeeCompensationPolicy
{
    use ChecksCompensationPermission;

    public function viewAny(User $user): bool
    {
        return $user->can('hr.compensation.view');
    }

    public function view(User $user, EmployeeCompensation $compensation): bool
    {
        return $user->can('hr.compensation.view')
            && $this->sameCompany($user, $compensation->company_id);
    }

    public function create(User $user): bool
    {
        return $user->can('hr.compensation.create');
    }

    public function update(User $user, EmployeeCompensation $compensation): bool
    {
        return $user->can('hr.compensation.edit')
            && $this->sameCompany($user, $compensation->company_id);
    }

    public function approve(User $user, EmployeeCompensation $compensation): bool
    {
        return $user->can('hr.compensation.approve')
            && $this->sameCompany($user, $compensation->company_id);
    }

    public function audit(User $user): bool
    {
        return $user->can('hr.compensation.audit');
    }
}

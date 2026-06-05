<?php

namespace App\Policies;

use App\Models\Hr\Shift;
use App\Models\User;

class ShiftPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('hr.attendance.view');
    }

    public function create(User $user): bool
    {
        return $user->can('hr.attendance.edit');
    }

    public function update(User $user, Shift $shift): bool
    {
        return $user->can('hr.attendance.edit') && $this->sameCompany($user, $shift->company_id);
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->company_id === $companyId;
    }
}

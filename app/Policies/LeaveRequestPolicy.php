<?php

namespace App\Policies;

use App\Models\Hr\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('hr.leave.view');
    }

    public function view(User $user, LeaveRequest $request): bool
    {
        return $user->can('hr.leave.view') && $this->sameCompany($user, $request->company_id);
    }

    public function create(User $user): bool
    {
        return $user->can('hr.leave.create');
    }

    public function approve(User $user, LeaveRequest $request): bool
    {
        return $user->can('hr.leave.approve') && $this->sameCompany($user, $request->company_id);
    }

    public function reject(User $user, LeaveRequest $request): bool
    {
        return $user->can('hr.leave.reject') && $this->sameCompany($user, $request->company_id);
    }

    public function export(User $user): bool
    {
        return $user->can('hr.leave.export');
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->company_id === $companyId;
    }
}

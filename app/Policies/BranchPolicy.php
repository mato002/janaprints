<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;

class BranchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('branches.manage');
    }

    public function view(User $user, Branch $branch): bool
    {
        return $user->can('branches.manage') && $this->sameCompany($user, $branch->company_id);
    }

    public function create(User $user): bool
    {
        return $user->can('branches.manage');
    }

    public function update(User $user, Branch $branch): bool
    {
        return $user->can('branches.manage') && $this->sameCompany($user, $branch->company_id);
    }

    public function delete(User $user, Branch $branch): bool
    {
        return $user->can('branches.manage') && $this->sameCompany($user, $branch->company_id);
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->company_id === $companyId;
    }
}

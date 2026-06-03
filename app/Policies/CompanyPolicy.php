<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('companies.manage');
    }

    public function view(User $user, Company $company): bool
    {
        return $user->can('companies.manage') && $this->canAccess($user, $company);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Super Admin') && $user->can('companies.manage');
    }

    public function update(User $user, Company $company): bool
    {
        return $user->can('companies.manage') && $this->canAccess($user, $company);
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->hasRole('Super Admin') && $user->can('companies.manage');
    }

    protected function canAccess(User $user, Company $company): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->company_id === $company->id;
    }
}

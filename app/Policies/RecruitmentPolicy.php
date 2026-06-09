<?php

namespace App\Policies;

use App\Models\User;

class RecruitmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('hr.recruitment.view');
    }

    public function view(User $user, object $record): bool
    {
        return $user->can('hr.recruitment.view') && $this->sameCompany($user, (int) $record->company_id);
    }

    public function create(User $user): bool
    {
        return $user->can('hr.recruitment.create');
    }

    public function update(User $user, object $record): bool
    {
        return $user->can('hr.recruitment.manage') && $this->sameCompany($user, (int) $record->company_id);
    }

    public function manage(User $user): bool
    {
        return $user->can('hr.recruitment.manage');
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->company_id === $companyId;
    }
}

<?php

namespace App\Policies;

use App\Models\JobTitle;
use App\Models\User;

class JobTitlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('organization.job_titles.view');
    }

    public function view(User $user, JobTitle $jobTitle): bool
    {
        return $user->can('organization.job_titles.view') && $this->sameCompany($user, $jobTitle);
    }

    public function create(User $user): bool
    {
        return $user->can('organization.job_titles.create');
    }

    public function update(User $user, JobTitle $jobTitle): bool
    {
        return $user->can('organization.job_titles.edit') && $this->sameCompany($user, $jobTitle);
    }

    public function deactivate(User $user, JobTitle $jobTitle): bool
    {
        return $user->can('organization.job_titles.deactivate') && $this->sameCompany($user, $jobTitle);
    }

    protected function sameCompany(User $user, JobTitle $jobTitle): bool
    {
        return $user->hasRole('Super Admin') || (int) $user->company_id === (int) $jobTitle->company_id;
    }
}

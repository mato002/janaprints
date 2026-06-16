<?php

namespace App\Policies;

use App\Models\Hr\TrainingProgram;
use App\Models\User;

class TrainingProgramPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('hr.training.view');
    }

    public function view(User $user, TrainingProgram $program): bool
    {
        return $user->can('hr.training.view') && $this->sameCompany($user, $program->company_id);
    }

    public function create(User $user): bool
    {
        return $user->can('hr.training.manage');
    }

    public function update(User $user, TrainingProgram $program): bool
    {
        return $user->can('hr.training.manage') && $this->sameCompany($user, $program->company_id);
    }

    public function archive(User $user, TrainingProgram $program): bool
    {
        return $user->can('hr.training.manage') && $this->sameCompany($user, $program->company_id);
    }

    public function export(User $user): bool
    {
        return $user->can('hr.training.export');
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->company_id === $companyId;
    }
}

<?php

namespace App\Policies;

use App\Models\Hr\EmployeeTrainingAssignment;
use App\Models\Hr\TrainingProgram;
use App\Models\User;

class EmployeeTrainingAssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('hr.training.view');
    }

    public function view(User $user, EmployeeTrainingAssignment $assignment): bool
    {
        return $user->can('hr.training.view') && $this->sameCompany($user, $assignment->company_id);
    }

    public function create(User $user): bool
    {
        return $user->can('hr.training.manage');
    }

    public function update(User $user, EmployeeTrainingAssignment $assignment): bool
    {
        return $user->can('hr.training.manage') && $this->sameCompany($user, $assignment->company_id);
    }

    public function delete(User $user, EmployeeTrainingAssignment $assignment): bool
    {
        return $user->can('hr.training.manage') && $this->sameCompany($user, $assignment->company_id);
    }

    public function managePrograms(User $user): bool
    {
        return $user->can('hr.training.manage');
    }

    public function viewPrograms(User $user): bool
    {
        return $user->can('hr.training.view');
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->company_id === $companyId;
    }
}

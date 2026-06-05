<?php

namespace App\Policies;

use App\Models\Hr\PayrollRun;
use App\Models\User;

class PayrollRunPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('hr.payroll.view');
    }

    public function view(User $user, PayrollRun $run): bool
    {
        return $user->can('hr.payroll.view') && $this->sameCompany($user, $run->company_id);
    }

    public function create(User $user): bool
    {
        return $user->can('hr.payroll.process');
    }

    public function process(User $user, PayrollRun $run): bool
    {
        return $user->can('hr.payroll.process') && $this->sameCompany($user, $run->company_id);
    }

    public function approve(User $user, PayrollRun $run): bool
    {
        return $user->can('hr.payroll.approve') && $this->sameCompany($user, $run->company_id);
    }

    public function export(User $user): bool
    {
        return $user->can('hr.payroll.export');
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->company_id === $companyId;
    }
}

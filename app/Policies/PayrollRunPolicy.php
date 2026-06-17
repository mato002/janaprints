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

    public function review(User $user, PayrollRun $run): bool
    {
        return $user->can('hr.payroll.review') && $this->sameCompany($user, $run->company_id);
    }

    public function approve(User $user, PayrollRun $run): bool
    {
        return $user->can('hr.payroll.approve') && $this->sameCompany($user, $run->company_id);
    }

    public function post(User $user, PayrollRun $run): bool
    {
        return $user->can('hr.payroll.post') && $this->sameCompany($user, $run->company_id);
    }

    public function release(User $user, PayrollRun $run): bool
    {
        return $user->can('hr.payroll.release') && $this->sameCompany($user, $run->company_id);
    }

    public function markPaid(User $user, PayrollRun $run): bool
    {
        return $user->can('hr.payroll.mark-paid') && $this->sameCompany($user, $run->company_id);
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

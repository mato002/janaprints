<?php

namespace App\Policies;

use App\Models\Accounting\FiscalYear;
use App\Models\User;

class FiscalYearPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('accounting.periods.view');
    }

    public function view(User $user, FiscalYear $fiscalYear): bool
    {
        return $user->can('accounting.periods.view') && $this->sameCompany($user, $fiscalYear->company_id);
    }

    public function create(User $user): bool
    {
        return $user->can('accounting.periods.create');
    }

    public function close(User $user, FiscalYear $fiscalYear): bool
    {
        return $user->can('accounting.periods.close') && $this->sameCompany($user, $fiscalYear->company_id);
    }

    public function lock(User $user, FiscalYear $fiscalYear): bool
    {
        return $user->can('accounting.periods.lock') && $this->sameCompany($user, $fiscalYear->company_id);
    }

    public function yearEndPrep(User $user, FiscalYear $fiscalYear): bool
    {
        return $user->can('accounting.periods.close') && $this->sameCompany($user, $fiscalYear->company_id);
    }

    public function reopen(User $user, FiscalYear $fiscalYear): bool
    {
        return $user->can('accounting.periods.reopen') && $this->sameCompany($user, $fiscalYear->company_id);
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->company_id === $companyId;
    }
}

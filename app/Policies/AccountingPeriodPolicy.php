<?php

namespace App\Policies;

use App\Models\Accounting\AccountingPeriod;
use App\Models\User;

class AccountingPeriodPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('accounting.periods.view');
    }

    public function view(User $user, AccountingPeriod $period): bool
    {
        return $user->can('accounting.periods.view') && $this->sameCompany($user, $period->company_id);
    }

    public function close(User $user, AccountingPeriod $period): bool
    {
        return $user->can('accounting.periods.close') && $this->sameCompany($user, $period->company_id);
    }

    public function lock(User $user, AccountingPeriod $period): bool
    {
        return $user->can('accounting.periods.lock') && $this->sameCompany($user, $period->company_id);
    }

    public function reopen(User $user, AccountingPeriod $period): bool
    {
        return $user->can('accounting.periods.reopen') && $this->sameCompany($user, $period->company_id);
    }

    public function setCurrent(User $user, AccountingPeriod $period): bool
    {
        return $user->can('accounting.periods.manage') && $this->sameCompany($user, $period->company_id);
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->company_id === $companyId;
    }
}

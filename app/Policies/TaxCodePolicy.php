<?php

namespace App\Policies;

use App\Models\Tax\TaxCode;
use App\Models\User;

class TaxCodePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('tax.codes.view');
    }

    public function view(User $user, TaxCode $taxCode): bool
    {
        return $user->can('tax.codes.view') && $this->sameCompany($user, $taxCode->company_id);
    }

    public function create(User $user): bool
    {
        return $user->can('tax.codes.manage');
    }

    public function update(User $user, TaxCode $taxCode): bool
    {
        return $user->can('tax.codes.manage') && $this->sameCompany($user, $taxCode->company_id);
    }

    public function viewReports(User $user): bool
    {
        return $user->can('tax.reports.view');
    }

    public function viewLedger(User $user): bool
    {
        return $user->can('tax.ledger.view');
    }

    public function viewPeriods(User $user): bool
    {
        return $user->can('tax.periods.view');
    }

    public function manageReturns(User $user): bool
    {
        return $user->can('tax.returns.manage');
    }

    public function viewAudit(User $user): bool
    {
        return $user->can('tax.audit.view');
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->company_id === $companyId;
    }
}

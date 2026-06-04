<?php

namespace App\Policies;

use App\Models\Accounting\GlAccount;
use App\Models\User;

class GlAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('accounting.chart.view');
    }

    public function view(User $user, GlAccount $account): bool
    {
        return $user->can('accounting.chart.view') && $this->sameCompany($user, $account->company_id);
    }

    public function create(User $user): bool
    {
        return $user->can('accounting.chart.create');
    }

    public function update(User $user, GlAccount $account): bool
    {
        return $user->can('accounting.chart.edit')
            && $this->sameCompany($user, $account->company_id)
            && $account->status->isEditable();
    }

    public function delete(User $user, GlAccount $account): bool
    {
        return $user->can('accounting.chart.delete')
            && $this->sameCompany($user, $account->company_id)
            && ! $account->is_system
            && $account->status->isEditable();
    }

    public function lock(User $user, GlAccount $account): bool
    {
        return $user->can('accounting.chart.lock')
            && $this->sameCompany($user, $account->company_id);
    }

    public function unlock(User $user, GlAccount $account): bool
    {
        return $user->can('accounting.chart.lock')
            && $this->sameCompany($user, $account->company_id);
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->company_id === $companyId;
    }
}

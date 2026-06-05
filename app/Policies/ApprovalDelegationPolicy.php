<?php

namespace App\Policies;

use App\Models\Platform\ApprovalDelegation;
use App\Models\User;

class ApprovalDelegationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('governance.delegations.view');
    }

    public function view(User $user, ApprovalDelegation $delegation): bool
    {
        return $user->can('governance.delegations.view')
            && $this->sameCompany($user, $delegation);
    }

    public function create(User $user): bool
    {
        return $user->can('governance.delegations.create');
    }

    public function update(User $user, ApprovalDelegation $delegation): bool
    {
        return $user->can('governance.delegations.manage')
            && $this->sameCompany($user, $delegation);
    }

    public function cancel(User $user, ApprovalDelegation $delegation): bool
    {
        return $user->can('governance.delegations.manage')
            && $this->sameCompany($user, $delegation);
    }

    protected function sameCompany(User $user, ApprovalDelegation $delegation): bool
    {
        return $user->hasRole('Super Admin') || $user->company_id === $delegation->company_id;
    }
}

<?php

namespace App\Policies;

use App\Governance\ApprovalChainsCenter;
use App\Models\Governance\ApprovalChain;
use App\Models\User;

class ApprovalChainsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('governance.chains.view');
    }

    public function view(User $user, ApprovalChain $chain): bool
    {
        return $user->can('governance.chains.view');
    }

    public function create(User $user): bool
    {
        return $user->can('governance.chains.create');
    }

    public function update(User $user, ApprovalChain $chain): bool
    {
        return $user->can('governance.chains.edit');
    }

    public function activate(User $user, ApprovalChain $chain): bool
    {
        return $user->can('governance.chains.activate');
    }

    public function manage(User $user): bool
    {
        return $user->can('governance.chains.edit')
            || $user->can('governance.chains.create')
            || $user->can('governance.chains.activate');
    }
}

<?php

namespace App\Policies;

use App\Models\Assets\AssetRegisterReconciliation;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class AssetReconciliationPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('assets.reconciliation.view');
    }

    public function view(User $user, AssetRegisterReconciliation $reconciliation): bool
    {
        return $user->can('assets.reconciliation.view') && $this->sameTenant($user, $reconciliation);
    }

    public function run(User $user): bool
    {
        return $user->can('assets.reconciliation.view');
    }
}

<?php

namespace App\Policies;

use App\Models\Assets\AssetCapitalizationReconciliation;
use App\Models\Assets\FixedAsset;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class AssetAcquisitionPolicy
{
    use ChecksCrmTenant;

    public function viewDashboard(User $user): bool
    {
        return $user->can('assets.acquisition.view');
    }

    public function post(User $user, ?FixedAsset $asset = null): bool
    {
        return $user->can('assets.acquisition.post');
    }

    public function reconcile(User $user): bool
    {
        return $user->can('assets.reconciliation.view');
    }

    public function view(User $user, AssetCapitalizationReconciliation $record): bool
    {
        return $this->viewReconciliation($user, $record);
    }

    public function viewReconciliation(User $user, AssetCapitalizationReconciliation $record): bool
    {
        return $user->can('assets.reconciliation.view') && $this->sameTenant($user, $record);
    }
}

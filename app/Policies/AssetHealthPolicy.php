<?php

namespace App\Policies;

use App\Models\Assets\FixedAsset;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class AssetHealthPolicy
{
    use ChecksCrmTenant;

    public function view(User $user, FixedAsset $asset): bool
    {
        return $user->can('assets.health.view') && $this->sameTenant($user, $asset);
    }
}

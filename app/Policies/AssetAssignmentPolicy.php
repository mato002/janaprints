<?php

namespace App\Policies;

use App\Models\Assets\FixedAsset;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class AssetAssignmentPolicy
{
    use ChecksCrmTenant;

    public function assign(User $user, FixedAsset $asset): bool
    {
        return $user->can('assets.assign') && $this->sameTenant($user, $asset);
    }

    public function viewCustody(User $user, FixedAsset $asset): bool
    {
        return $user->can('assets.custody.view') && $this->sameTenant($user, $asset);
    }

    public function manageCustody(User $user, FixedAsset $asset): bool
    {
        return $user->can('assets.custody.manage') && $this->sameTenant($user, $asset);
    }
}

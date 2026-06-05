<?php

namespace App\Policies;

use App\Models\Assets\FixedAsset;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class Asset360Policy
{
    use ChecksCrmTenant;

    public function view(User $user, FixedAsset $asset): bool
    {
        return $user->can('assets.360.view') && $this->sameTenant($user, $asset);
    }

    public function viewAny(User $user): bool
    {
        return $user->can('assets.360.view');
    }
}

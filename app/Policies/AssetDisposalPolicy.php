<?php

namespace App\Policies;

use App\Models\Assets\AssetDisposal;
use App\Models\Assets\FixedAsset;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class AssetDisposalPolicy
{
    use ChecksCrmTenant;

    public function post(User $user, ?AssetDisposal $disposal = null): bool
    {
        return $user->can('assets.disposal.post');
    }

    public function dispose(User $user, FixedAsset $asset): bool
    {
        return ($user->can('assets.disposal.post') || $user->can('assets.manage'))
            && $this->sameTenant($user, $asset);
    }
}

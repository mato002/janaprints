<?php

namespace App\Policies;

use App\Models\Assets\AssetReturn;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class AssetReturnPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('assets.custody.view');
    }

    public function view(User $user, AssetReturn $assetReturn): bool
    {
        return $user->can('assets.custody.view') && $this->sameTenant($user, $assetReturn);
    }

    public function create(User $user): bool
    {
        return $user->can('assets.return');
    }
}

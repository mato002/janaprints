<?php

namespace App\Policies;

use App\Models\Assets\FixedAsset;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class FixedAssetPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('assets.view');
    }

    public function view(User $user, FixedAsset $asset): bool
    {
        return $user->can('assets.view') && $this->sameTenant($user, $asset);
    }

    public function view360(User $user, FixedAsset $asset): bool
    {
        return $user->can('assets.360.view') && $this->sameTenant($user, $asset);
    }

    public function create(User $user): bool
    {
        return $user->can('assets.create');
    }

    public function update(User $user, FixedAsset $asset): bool
    {
        return $user->can('assets.edit') && $this->sameTenant($user, $asset);
    }

    public function manage(User $user, FixedAsset $asset): bool
    {
        return $user->can('assets.manage') && $this->sameTenant($user, $asset);
    }

    public function assign(User $user, FixedAsset $asset): bool
    {
        return ($user->can('assets.assign') || $user->can('assets.manage'))
            && $this->sameTenant($user, $asset);
    }

    public function dispose(User $user, FixedAsset $asset): bool
    {
        return ($user->can('assets.disposal.post') || $user->can('assets.manage'))
            && $this->sameTenant($user, $asset);
    }

    public function archive(User $user, FixedAsset $asset): bool
    {
        return $this->manage($user, $asset);
    }

    public function export(User $user): bool
    {
        return $user->can('assets.view');
    }
}

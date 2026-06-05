<?php

namespace App\Policies;

use App\Models\Assets\AssetWriteOff;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class AssetWriteOffPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('assets.depreciation.view');
    }

    public function view(User $user, AssetWriteOff $writeOff): bool
    {
        return $user->can('assets.depreciation.view') && $this->sameTenant($user, $writeOff);
    }

    public function manage(User $user): bool
    {
        return $user->can('assets.writeoff.manage');
    }

    public function post(User $user, AssetWriteOff $writeOff): bool
    {
        return $user->can('assets.writeoff.manage') && $this->sameTenant($user, $writeOff);
    }
}

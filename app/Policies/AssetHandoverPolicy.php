<?php

namespace App\Policies;

use App\Models\Assets\AssetHandover;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class AssetHandoverPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('assets.custody.view');
    }

    public function view(User $user, AssetHandover $handover): bool
    {
        return $user->can('assets.custody.view') && $this->sameTenant($user, $handover);
    }

    public function create(User $user): bool
    {
        return $user->can('assets.handover.manage');
    }

    public function manage(User $user, AssetHandover $handover): bool
    {
        return $user->can('assets.handover.manage') && $this->sameTenant($user, $handover);
    }
}

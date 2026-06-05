<?php

namespace App\Policies;

use App\Models\Assets\AssetBranchTransfer;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class AssetTransferPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('assets.custody.view');
    }

    public function view(User $user, AssetBranchTransfer $transfer): bool
    {
        return $user->can('assets.custody.view') && $this->sameTenant($user, $transfer);
    }

    public function create(User $user): bool
    {
        return $user->can('assets.transfer');
    }

    public function manage(User $user, AssetBranchTransfer $transfer): bool
    {
        return $user->can('assets.transfer') && $this->sameTenant($user, $transfer);
    }

    public function approve(User $user, AssetBranchTransfer $transfer): bool
    {
        return $user->can('assets.custody.manage') && $this->sameTenant($user, $transfer);
    }
}

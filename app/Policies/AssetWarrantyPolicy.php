<?php

namespace App\Policies;

use App\Models\Assets\AssetWarranty;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class AssetWarrantyPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('assets.acquisition.view');
    }

    public function manage(User $user, ?AssetWarranty $warranty = null): bool
    {
        if (! $user->can('assets.warranty.manage')) {
            return false;
        }

        return $warranty === null || $this->sameTenant($user, $warranty);
    }
}

<?php

namespace App\Policies;

use App\Models\Assets\DepreciationRun;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class AssetDepreciationPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('assets.depreciation.view');
    }

    public function view(User $user, DepreciationRun $run): bool
    {
        return $user->can('assets.depreciation.view') && $this->sameTenant($user, $run);
    }

    public function run(User $user): bool
    {
        return $user->can('assets.depreciation.run');
    }

    public function post(User $user, DepreciationRun $run): bool
    {
        return $user->can('assets.depreciation.post') && $this->sameTenant($user, $run);
    }
}

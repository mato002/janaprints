<?php

namespace App\Policies;

use App\Models\Assets\AssetDowntimeRecord;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class DowntimePolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('maintenance.view');
    }

    public function view(User $user, AssetDowntimeRecord $record): bool
    {
        return $user->can('maintenance.view') && $this->sameTenant($user, $record);
    }

    public function create(User $user): bool
    {
        return $user->can('maintenance.manage');
    }
}

<?php

namespace App\Policies;

use App\Models\Crm\CustomerActivity;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class CustomerActivityPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('commercial.activities.view');
    }

    public function view(User $user, CustomerActivity $activity): bool
    {
        return $user->can('commercial.activities.view') && $this->sameTenant($user, $activity);
    }

    public function create(User $user): bool
    {
        return $user->can('commercial.activities.create');
    }

    public function update(User $user, CustomerActivity $activity): bool
    {
        return $user->can('commercial.activities.edit') && $this->sameTenant($user, $activity);
    }

    public function delete(User $user, CustomerActivity $activity): bool
    {
        return $user->can('commercial.activities.delete') && $this->sameTenant($user, $activity);
    }
}

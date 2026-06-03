<?php

namespace App\Policies;

use App\Models\Crm\CustomerSegment;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class CustomerSegmentPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('crm.customers.view');
    }

    public function view(User $user, CustomerSegment $segment): bool
    {
        return $user->can('crm.customers.view') && $this->sameTenant($user, $segment);
    }

    public function create(User $user): bool
    {
        return $user->can('crm.customers.create');
    }

    public function update(User $user, CustomerSegment $segment): bool
    {
        return $user->can('crm.customers.edit') && $this->sameTenant($user, $segment);
    }

    public function delete(User $user, CustomerSegment $segment): bool
    {
        return $user->can('crm.customers.delete') && $this->sameTenant($user, $segment);
    }
}

<?php

namespace App\Policies;

use App\Enums\VendorStatus;
use App\Models\Procurement\Vendor;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class VendorPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('procurement.vendors.view');
    }

    public function view(User $user, Vendor $vendor): bool
    {
        return $user->can('procurement.vendors.view') && $this->sameTenant($user, $vendor);
    }

    public function create(User $user): bool
    {
        return $user->can('procurement.vendors.create');
    }

    public function update(User $user, Vendor $vendor): bool
    {
        return $user->can('procurement.vendors.edit') && $this->sameTenant($user, $vendor);
    }

    public function delete(User $user, Vendor $vendor): bool
    {
        return $user->can('procurement.vendors.delete')
            && $this->sameTenant($user, $vendor)
            && $vendor->status === VendorStatus::Inactive;
    }
}

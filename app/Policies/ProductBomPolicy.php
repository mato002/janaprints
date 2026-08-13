<?php

namespace App\Policies;

use App\Models\Production\ProductBom;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class ProductBomPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('production.bom.view');
    }

    public function view(User $user, ProductBom $bom): bool
    {
        return $user->can('production.bom.view') && $this->sameTenant($user, $bom);
    }

    public function create(User $user): bool
    {
        return $user->can('production.bom.create') || $user->can('production.edit');
    }

    public function update(User $user, ProductBom $bom): bool
    {
        return $user->can('production.bom.edit') && $this->sameTenant($user, $bom);
    }

    public function delete(User $user, ProductBom $bom): bool
    {
        return $user->can('production.bom.edit') && $this->sameTenant($user, $bom);
    }
}

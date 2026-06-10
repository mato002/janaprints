<?php

namespace App\Policies;

use App\Models\Production\ProductionOutput;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class ProductionOutputPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('production.outputs.view');
    }

    public function view(User $user, ProductionOutput $output): bool
    {
        return $user->can('production.outputs.view') && $this->sameTenant($user, $output);
    }

    public function create(User $user): bool
    {
        return $user->can('production.outputs.create');
    }

    public function post(User $user): bool
    {
        return $user->can('production.outputs.post');
    }
}

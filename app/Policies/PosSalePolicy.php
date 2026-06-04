<?php

namespace App\Policies;

use App\Models\Pos\PosSale;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class PosSalePolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('pos.view');
    }

    public function view(User $user, PosSale $sale): bool
    {
        return $user->can('pos.view') && $this->sameTenant($user, $sale);
    }

    public function create(User $user): bool
    {
        return $user->can('pos.create');
    }

    public function update(User $user, PosSale $sale): bool
    {
        return $user->can('pos.edit') && $this->sameTenant($user, $sale);
    }

    public function cancel(User $user, PosSale $sale): bool
    {
        return $user->can('pos.cancel') && $this->sameTenant($user, $sale);
    }

    public function refund(User $user, PosSale $sale): bool
    {
        return $user->can('pos.refund') && $this->sameTenant($user, $sale);
    }
}

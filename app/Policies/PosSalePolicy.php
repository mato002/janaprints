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
        return $user->can('pos.view') || $user->can('pos.counter_sales.view');
    }

    public function view(User $user, PosSale $sale): bool
    {
        return ($user->can('pos.view') || $user->can('pos.counter_sales.view')) && $this->sameTenant($user, $sale);
    }

    public function counterSalesView(User $user): bool
    {
        return $user->can('pos.counter_sales.view') || $user->can('pos.view');
    }

    public function create(User $user): bool
    {
        return $user->can('pos.create') || $user->can('pos.counter_sales.create');
    }

    public function hold(User $user): bool
    {
        return $user->can('pos.counter_sales.hold') || $user->can('pos.create');
    }

    public function completeSale(User $user): bool
    {
        return $user->can('pos.counter_sales.complete') || $user->can('pos.edit') || $user->can('pos.create');
    }

    public function complete(User $user, PosSale $sale): bool
    {
        return $this->completeSale($user) && $this->sameTenant($user, $sale);
    }

    public function update(User $user, PosSale $sale): bool
    {
        return ($user->can('pos.edit') || $user->can('pos.counter_sales.complete') || $user->can('pos.counter_sales.hold'))
            && $this->sameTenant($user, $sale);
    }

    public function cancelSale(User $user): bool
    {
        return $user->can('pos.cancel') || $user->can('pos.counter_sales.cancel');
    }

    public function cancel(User $user, PosSale $sale): bool
    {
        return $this->cancelSale($user) && $this->sameTenant($user, $sale);
    }

    public function refund(User $user, PosSale $sale): bool
    {
        return $user->can('pos.refund') && $this->sameTenant($user, $sale);
    }
}

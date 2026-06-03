<?php

namespace App\Policies;

use App\Enums\SalesOrderStatus;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class SalesOrderPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('sales_orders.view');
    }

    public function view(User $user, SalesOrder $salesOrder): bool
    {
        return $user->can('sales_orders.view') && $this->sameTenant($user, $salesOrder);
    }

    public function create(User $user): bool
    {
        return $user->can('sales_orders.create');
    }

    public function update(User $user, SalesOrder $salesOrder): bool
    {
        return $user->can('sales_orders.edit')
            && $this->sameTenant($user, $salesOrder)
            && $salesOrder->status->isEditable();
    }

    public function delete(User $user, SalesOrder $salesOrder): bool
    {
        return $user->can('sales_orders.delete')
            && $this->sameTenant($user, $salesOrder)
            && $salesOrder->status === SalesOrderStatus::Draft;
    }

    public function confirm(User $user, SalesOrder $salesOrder): bool
    {
        return $user->can('sales_orders.confirm')
            && $this->sameTenant($user, $salesOrder)
            && $salesOrder->status === SalesOrderStatus::Draft;
    }

    public function production(User $user, SalesOrder $salesOrder): bool
    {
        return $user->can('sales_orders.production')
            && $this->sameTenant($user, $salesOrder)
            && in_array($salesOrder->status, [
                SalesOrderStatus::Confirmed,
                SalesOrderStatus::ReadyForProduction,
                SalesOrderStatus::InProduction,
                SalesOrderStatus::Completed,
            ], true);
    }

    public function close(User $user, SalesOrder $salesOrder): bool
    {
        return $user->can('sales_orders.close')
            && $this->sameTenant($user, $salesOrder)
            && $salesOrder->status === SalesOrderStatus::Delivered;
    }

    public function transition(User $user, SalesOrder $salesOrder): bool
    {
        return $user->can('sales_orders.edit')
            && $this->sameTenant($user, $salesOrder);
    }
}

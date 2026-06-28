<?php

namespace App\Policies;

use App\Models\Production\ProductionSpecification;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class ProductionSpecificationPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('production.view') || $user->can('sales_orders.view');
    }

    public function view(User $user, ProductionSpecification $specification): bool
    {
        return ($user->can('production.view') || $user->can('sales_orders.view'))
            && $this->sameTenant($user, $specification);
    }

    public function create(User $user, SalesOrder $salesOrder): bool
    {
        return $user->can('sales_orders.edit')
            && $this->sameTenant($user, $salesOrder);
    }

    public function update(User $user, ProductionSpecification $specification): bool
    {
        return $user->can('sales_orders.edit')
            && $this->sameTenant($user, $specification);
    }
}

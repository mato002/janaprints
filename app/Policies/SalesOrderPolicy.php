<?php

namespace App\Policies;

use App\Enums\SalesOrderStatus;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;
use App\Policies\Concerns\ChecksWorkflowAttempt;

class SalesOrderPolicy
{
    use ChecksCrmTenant, ChecksWorkflowAttempt;

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

    public function updateProductionSetup(User $user, SalesOrder $salesOrder): bool
    {
        return $user->can('sales_orders.edit')
            && $this->sameTenant($user, $salesOrder)
            && ! in_array($salesOrder->status, [
                SalesOrderStatus::Closed,
                SalesOrderStatus::Cancelled,
            ], true);
    }

    public function delete(User $user, SalesOrder $salesOrder): bool
    {
        return $user->can('sales_orders.delete')
            && $this->sameTenant($user, $salesOrder)
            && $salesOrder->status === SalesOrderStatus::Draft;
    }

    public function confirm(User $user, SalesOrder $salesOrder): bool
    {
        return $this->canAttemptWorkflow(
            $user,
            $salesOrder,
            'sales_orders.confirm',
            fn (SalesOrder $order) => $order->status !== SalesOrderStatus::Draft,
        );
    }

    public function production(User $user, SalesOrder $salesOrder): bool
    {
        return $this->canAttemptWorkflow(
            $user,
            $salesOrder,
            'sales_orders.production',
            fn (SalesOrder $order) => in_array($order->status, [
                SalesOrderStatus::Draft,
                SalesOrderStatus::Closed,
                SalesOrderStatus::Cancelled,
            ], true),
        );
    }

    public function close(User $user, SalesOrder $salesOrder): bool
    {
        return $this->canAttemptWorkflow(
            $user,
            $salesOrder,
            'sales_orders.close',
            fn (SalesOrder $order) => in_array($order->status, [
                SalesOrderStatus::Closed,
                SalesOrderStatus::Cancelled,
            ], true),
        );
    }

    public function transition(User $user, SalesOrder $salesOrder): bool
    {
        return $user->can('sales_orders.edit')
            && $this->sameTenant($user, $salesOrder)
            && ! in_array($salesOrder->status, [
                SalesOrderStatus::Closed,
                SalesOrderStatus::Cancelled,
            ], true);
    }
}

<?php

namespace App\Policies;

use App\Models\Crm\Customer;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class CustomerPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('crm.customers.view');
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->can('crm.customers.view') && $this->sameTenant($user, $customer);
    }

    public function create(User $user): bool
    {
        return $user->can('crm.customers.create');
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->can('crm.customers.edit') && $this->sameTenant($user, $customer);
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->can('crm.customers.delete') && $this->sameTenant($user, $customer);
    }

    public function viewReceivablesLedger(User $user): bool
    {
        return $user->can('receivables.ledger.view');
    }

    public function viewReceivablesStatement(User $user): bool
    {
        return $user->can('receivables.statement.view');
    }

    public function viewReceivablesAging(User $user): bool
    {
        return $user->can('receivables.aging.view');
    }
}

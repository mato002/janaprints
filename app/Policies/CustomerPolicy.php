<?php

namespace App\Policies;

use App\Models\Crm\Customer;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;
use App\Support\Crm\CustomerOperationalGuard;

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
        return $user->can('crm.customers.delete')
            && $this->sameTenant($user, $customer)
            && ! app(CustomerOperationalGuard::class)->hasOperationalHistory($customer);
    }

    public function deactivate(User $user, Customer $customer): bool
    {
        return $user->can('crm.customers.edit')
            && $this->sameTenant($user, $customer)
            && $customer->status->value !== 'inactive';
    }

    public function inviteToPortal(User $user, Customer $customer): bool
    {
        return $this->update($user, $customer);
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

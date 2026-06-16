<?php

namespace App\Http\Controllers\Client\Concerns;

use App\Models\Crm\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait ResolvesClientCustomer
{
    protected function clientUser(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User && $user->isClientPortalAccount(), 403);

        return $user;
    }

    protected function clientCustomer(): Customer
    {
        $customer = $this->clientUser()->customer;

        abort_unless($customer instanceof Customer, 403, __('Your account is not linked to a customer record.'));

        return $customer;
    }

    protected function assertClientOwns(Model $model, Customer $customer): void
    {
        abort_unless(
            isset($model->customer_id) && (int) $model->customer_id === (int) $customer->id,
            404,
        );
    }
}

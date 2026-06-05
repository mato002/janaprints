<?php

namespace App\Policies;

use App\Models\Commercial\CommercialPriceBook;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class CommercialPriceBookPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('commercial.price_books.view');
    }

    public function view(User $user, CommercialPriceBook $priceBook): bool
    {
        return $user->can('commercial.price_books.view') && $this->sameTenant($user, $priceBook);
    }

    public function create(User $user): bool
    {
        return $user->can('commercial.price_books.create');
    }

    public function update(User $user, CommercialPriceBook $priceBook): bool
    {
        return $user->can('commercial.price_books.edit') && $this->sameTenant($user, $priceBook);
    }

    public function delete(User $user, CommercialPriceBook $priceBook): bool
    {
        return $user->can('commercial.price_books.delete') && $this->sameTenant($user, $priceBook);
    }
}

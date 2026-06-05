<?php

namespace App\Support\Storefront;

use Illuminate\Support\Facades\Route;

class StorefrontUrls
{
    public static function quoteForm(): string
    {
        if (! Route::has('storefront.quote')) {
            return '#quote-form';
        }

        return route('storefront.quote').'#quote-form';
    }
}

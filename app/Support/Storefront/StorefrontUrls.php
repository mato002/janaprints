<?php

namespace App\Support\Storefront;

use Illuminate\Support\Facades\Route;

class StorefrontUrls
{
    public static function quoteForm(): string
    {
        if (Route::has('storefront.quote')) {
            return route('storefront.quote').'#quote-form';
        }

        return self::homeUrl().'#quote-form';
    }

    public static function contactSection(?string $type = null): string
    {
        $home = self::homeUrl();

        if ($type) {
            return $home.'?type='.urlencode($type).'#contact';
        }

        return $home.'#contact';
    }

    public static function aboutSection(): string
    {
        if (Route::has('storefront.about')) {
            return route('storefront.about');
        }

        return self::homeUrl().'#about';
    }

    private static function homeUrl(): string
    {
        return Route::has('home') ? route('home') : '/';
    }
}

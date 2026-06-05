<?php

namespace App\Support\Storefront;

class StorefrontCatalog
{
    public static function findService(string $slug): ?array
    {
        $capability = collect(config('capabilities.capabilities', []))
            ->firstWhere('slug', $slug);

        if (! $capability) {
            return null;
        }

        $seo = config("storefront.service_seo.{$slug}", []);

        return array_merge($capability, [
            'seo' => $seo,
            'benefits' => $seo['benefits'] ?? [],
            'use_cases' => $seo['use_cases'] ?? [],
        ]);
    }

    public static function findProduct(string $slug): ?array
    {
        return collect(config('products.items', []))->firstWhere('slug', $slug);
    }
}

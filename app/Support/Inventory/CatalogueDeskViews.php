<?php

namespace App\Support\Inventory;

/**
 * Canonical views for the consolidated Catalogue Desk.
 *
 * Operational: Products, Price Lists.
 * Lookups: Categories, Subcategories, Brands, Attributes, Units.
 */
final class CatalogueDeskViews
{
    public const PRODUCTS = 'products';

    public const PRICE_LISTS = 'price-lists';

    public const CATEGORIES = 'categories';

    public const SUBCATEGORIES = 'subcategories';

    public const BRANDS = 'brands';

    public const ATTRIBUTES = 'attributes';

    public const UNITS = 'units';

    /**
     * @return list<string>
     */
    public static function operational(): array
    {
        return [self::PRODUCTS, self::PRICE_LISTS];
    }

    /**
     * @return list<string>
     */
    public static function lookups(): array
    {
        return [
            self::CATEGORIES,
            self::SUBCATEGORIES,
            self::BRANDS,
            self::ATTRIBUTES,
            self::UNITS,
        ];
    }

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return array_merge(self::operational(), self::lookups());
    }

    public static function normalize(?string $view): string
    {
        $view = is_string($view) ? trim($view) : '';

        return in_array($view, self::all(), true) ? $view : self::PRODUCTS;
    }

    public static function isLookup(string $view): bool
    {
        return in_array(self::normalize($view), self::lookups(), true);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public static function deskUrl(string $view = self::PRODUCTS, array $query = []): string
    {
        return match (self::normalize($view)) {
            self::PRICE_LISTS => route('admin.inventory.catalogue.price-lists.index', $query),
            self::CATEGORIES => route('admin.inventory.catalogue.categories.index', $query),
            self::SUBCATEGORIES => route('admin.inventory.catalogue.subcategories.index', $query),
            self::BRANDS => route('admin.inventory.catalogue.brands.index', $query),
            self::ATTRIBUTES => route('admin.inventory.catalogue.attributes.index', $query),
            self::UNITS => route('admin.inventory.catalogue.units.index', $query),
            default => route('admin.inventory.items.index', $query),
        };
    }
}

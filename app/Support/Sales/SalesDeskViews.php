<?php

namespace App\Support\Sales;

/**
 * Canonical query views for the consolidated Sales Desk.
 */
final class SalesDeskViews
{
    public const DESK = 'desk';

    public const QUOTES = 'quotes';

    public const ORDERS = 'orders';

    public const ARTWORK = 'artwork';

    public const APPROVALS = 'approvals';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [self::DESK, self::QUOTES, self::ORDERS, self::ARTWORK, self::APPROVALS];
    }

    public static function normalize(?string $view): string
    {
        $view = is_string($view) ? trim($view) : '';

        return in_array($view, self::all(), true) ? $view : self::DESK;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public static function deskQuery(string $view = self::DESK, array $query = []): array
    {
        unset($query['view']);

        $params = array_merge($query, ['view' => self::normalize($view)]);

        if (($params['view'] ?? self::DESK) === self::DESK) {
            unset($params['view']);
        }

        return array_filter(
            $params,
            fn ($value) => $value !== null && $value !== '',
        );
    }

    public static function deskUrl(string $view = self::DESK, array $query = []): string
    {
        return route('admin.sales.desk', self::deskQuery($view, $query));
    }

    public static function quotesUrl(array $query = []): string
    {
        return self::deskUrl(self::QUOTES, $query);
    }

    public static function ordersUrl(array $query = []): string
    {
        return self::deskUrl(self::ORDERS, $query);
    }

    public static function artworkUrl(array $query = []): string
    {
        return self::deskUrl(self::ARTWORK, $query);
    }

    public static function approvalsUrl(array $query = []): string
    {
        return self::deskUrl(self::APPROVALS, $query);
    }
}

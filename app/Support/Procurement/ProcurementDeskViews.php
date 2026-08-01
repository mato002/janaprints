<?php

namespace App\Support\Procurement;

/**
 * Canonical views for the consolidated Buy Desk.
 */
final class ProcurementDeskViews
{
    public const DESK = 'desk';

    public const REQUESTS = 'requests';

    public const SUPPLIERS = 'suppliers';

    public const RFQS = 'rfqs';

    public const ORDERS = 'orders';

    public const RECEIPTS = 'receipts';

    public const APPROVALS = 'approvals';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::DESK,
            self::REQUESTS,
            self::SUPPLIERS,
            self::RFQS,
            self::ORDERS,
            self::RECEIPTS,
            self::APPROVALS,
        ];
    }

    public static function normalize(?string $view): string
    {
        $view = is_string($view) ? trim($view) : '';

        return in_array($view, self::all(), true) ? $view : self::DESK;
    }

    public static function isPanelView(?string $view): bool
    {
        return self::normalize($view) !== self::DESK;
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public static function deskUrl(string $view = self::DESK, array $query = []): string
    {
        $view = self::normalize($view);

        if ($view === self::DESK) {
            return route('admin.procurement.desk', $query);
        }

        return route('admin.procurement.desk', array_merge($query, ['view' => $view]));
    }
}

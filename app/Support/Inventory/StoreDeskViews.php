<?php

namespace App\Support\Inventory;

/**
 * Canonical views for the consolidated Store Desk.
 */
final class StoreDeskViews
{
    public const DESK = 'desk';

    public const BALANCES = 'balances';

    public const RECEIPTS = 'receipts';

    public const ISSUES = 'issues';

    public const TRANSFERS = 'transfers';

    public const ADJUSTMENTS = 'adjustments';

    public const MOVEMENTS = 'movements';

    public const ALERTS = 'alerts';

    /**
     * Document registers rendered inline on the Store Desk page.
     *
     * @return list<string>
     */
    public static function inlineRegisters(): array
    {
        return [
            self::RECEIPTS,
            self::ISSUES,
            self::TRANSFERS,
            self::ADJUSTMENTS,
        ];
    }

    public static function isInlineRegister(?string $view): bool
    {
        return in_array(self::normalize($view), self::inlineRegisters(), true);
    }

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::DESK,
            self::BALANCES,
            self::RECEIPTS,
            self::ISSUES,
            self::TRANSFERS,
            self::ADJUSTMENTS,
            self::MOVEMENTS,
            self::ALERTS,
        ];
    }

    public static function normalize(?string $view): string
    {
        $view = is_string($view) ? trim($view) : '';

        return in_array($view, self::all(), true) ? $view : self::DESK;
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public static function deskUrl(string $view = self::DESK, array $query = []): string
    {
        $view = self::normalize($view);

        return match ($view) {
            self::BALANCES => route('admin.inventory.store.balances', $query),
            self::RECEIPTS,
            self::ISSUES,
            self::TRANSFERS,
            self::ADJUSTMENTS => route('admin.store.desk', array_merge($query, ['view' => $view])),
            self::MOVEMENTS => route('admin.inventory.movements.index', $query),
            self::ALERTS => route('admin.inventory.alerts.index', $query),
            default => route('admin.store.desk', $query),
        };
    }
}

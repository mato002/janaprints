<?php

namespace App\Support\Procurement;

/**
 * Canonical views for the consolidated Procurement Desk.
 */
final class ProcurementDeskViews
{
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

        return in_array($view, self::all(), true) ? $view : self::REQUESTS;
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public static function deskUrl(string $view = self::REQUESTS, array $query = []): string
    {
        return match (self::normalize($view)) {
            self::SUPPLIERS => route('admin.procurement.vendors.index', $query),
            self::RFQS => route('admin.procurement.rfqs.index', $query),
            self::ORDERS => route('admin.procurement.orders.index', $query),
            self::RECEIPTS => route('admin.procurement.receipts.index', $query),
            self::APPROVALS => route('admin.procurement.approvals.index', $query),
            default => route('admin.procurement.requests.index', $query),
        };
    }
}

<?php

namespace App\Support\Procurement;

use App\Enums\PurchaseOrderStatus;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\Vendor;
use App\Models\User;

class BuyDeskService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function fastActions(?User $user): array
    {
        $actions = [];

        if ($user?->can('procurement.requests.create') || $user?->can('create', PurchaseRequest::class)) {
            $actions[] = [
                'key' => 'request',
                'label' => __('New Request'),
                'icon' => 'clipboard-list',
                'url' => route('admin.procurement.requests.create'),
                'modal' => false,
                'primary' => true,
            ];
        }

        if ($user?->can('procurement.vendors.create') || $user?->can('create', Vendor::class)) {
            $actions[] = [
                'key' => 'supplier',
                'label' => __('New Supplier'),
                'icon' => 'truck',
                'url' => route('admin.procurement.vendors.create'),
                'modal' => true,
                'primary' => true,
            ];
        }

        if ($user?->can('procurement.orders.create') || $user?->can('create', PurchaseOrder::class)) {
            $actions[] = [
                'key' => 'order',
                'label' => __('New PO'),
                'icon' => 'shopping-cart',
                'url' => route('admin.procurement.orders.create'),
                'modal' => false,
            ];
        }

        if ($user?->can('procurement.approvals.view')) {
            $actions[] = [
                'key' => 'approvals',
                'label' => __('Approvals'),
                'icon' => 'badge-check',
                'url' => ProcurementDeskViews::deskUrl(ProcurementDeskViews::APPROVALS),
                'modal' => false,
            ];
        }

        if ($user?->can('procurement.rfq.view') || $user?->can('procurement.vendors.view')) {
            $actions[] = [
                'key' => 'rfqs',
                'label' => __('RFQs'),
                'icon' => 'document-text',
                'url' => ProcurementDeskViews::deskUrl(ProcurementDeskViews::RFQS),
                'modal' => false,
            ];
        }

        if ($user?->can('procurement.orders.view') || $user?->can('procurement.vendors.view')) {
            $actions[] = [
                'key' => 'receipts',
                'label' => __('Receipts'),
                'icon' => 'inbox',
                'url' => ProcurementDeskViews::deskUrl(ProcurementDeskViews::RECEIPTS),
                'modal' => false,
            ];
        }

        return $actions;
    }

    /**
     * Open POs awaiting receipt — pipeline strip for the desk.
     *
     * @return list<array<string, mixed>>
     */
    public function receivingPipeline(?User $user = null): array
    {
        if (! ($user?->can('procurement.orders.view') || $user?->can('procurement.vendors.view') || $user?->can('viewAny', PurchaseOrder::class))) {
            return [];
        }

        $today = now()->toDateString();

        return PurchaseOrder::query()
            ->forTenant()
            ->with(['vendor:id,vendor_name'])
            ->whereIn('status', [
                PurchaseOrderStatus::Approved,
                PurchaseOrderStatus::Sent,
                PurchaseOrderStatus::PartiallyReceived,
            ])
            ->orderByRaw('CASE WHEN expected_delivery_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expected_delivery_date')
            ->limit(8)
            ->get()
            ->map(function (PurchaseOrder $order) use ($today) {
                $expected = $order->expected_delivery_date?->toDateString();
                $overdue = $expected !== null && $expected < $today;
                $dueToday = $expected === $today;

                return [
                    'label' => $order->po_number,
                    'supplier' => $order->vendor?->vendor_name ?? __('Supplier'),
                    'timing' => $expected
                        ? $order->expected_delivery_date->format('d M Y')
                        : __('No date'),
                    'status' => match (true) {
                        $overdue => __('Overdue'),
                        $dueToday => __('Due today'),
                        $expected !== null => __('On time'),
                        default => $order->status?->name ?? (string) $order->status,
                    },
                    'overdue' => $overdue,
                    'url' => route('admin.procurement.orders.show', $order),
                ];
            })
            ->all();
    }

    /**
     * Pipeline stage counts for the buy journey strip.
     *
     * @param  array<string, int>  $counts
     * @return list<array<string, mixed>>
     */
    public function pipelineStages(array $counts): array
    {
        return [
            [
                'key' => 'requests',
                'label' => __('Requests'),
                'count' => (int) ($counts['open_requests'] ?? 0),
                'url' => ProcurementDeskViews::deskUrl(ProcurementDeskViews::REQUESTS),
            ],
            [
                'key' => 'rfqs',
                'label' => __('RFQs'),
                'count' => (int) ($counts['open_rfqs'] ?? 0),
                'url' => ProcurementDeskViews::deskUrl(ProcurementDeskViews::RFQS),
            ],
            [
                'key' => 'orders',
                'label' => __('Orders'),
                'count' => (int) ($counts['open_orders'] ?? 0),
                'url' => ProcurementDeskViews::deskUrl(ProcurementDeskViews::ORDERS),
            ],
            [
                'key' => 'receipts',
                'label' => __('To receive'),
                'count' => (int) ($counts['awaiting_receipt'] ?? 0),
                'url' => ProcurementDeskViews::deskUrl(ProcurementDeskViews::RECEIPTS),
            ],
        ];
    }
}

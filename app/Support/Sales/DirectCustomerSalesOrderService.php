<?php

namespace App\Support\Sales;

use App\Enums\DocumentType;
use App\Enums\DomainCommunicationEvent;
use App\Enums\SalesOrderStatus;
use App\Models\Crm\Customer;
use App\Models\Sales\SalesOrder;
use App\Support\Communications\CommunicationEventDispatcher;
use App\Support\Platform\NumberingService;
use Illuminate\Support\Facades\DB;

class DirectCustomerSalesOrderService
{
    public function __construct(
        protected CustomerOrderContextService $context,
        protected NumberingService $numbering,
        protected CommunicationEventDispatcher $communications,
        protected CustomerOrderBillingDefaultsService $billingDefaults,
    ) {}

    /**
     * @param  array{quantity?: float|int, required_date?: ?string, notes?: ?string}  $overrides
     */
    public function repeatFrom(SalesOrder $source, int $createdBy, array $overrides = []): SalesOrder
    {
        return DB::transaction(function () use ($source, $createdBy, $overrides) {
            $source->loadMissing(['items', 'customer']);

            $quantity = isset($overrides['quantity']) ? (float) $overrides['quantity'] : null;
            $items = $this->buildRepeatItems($source, $quantity);
            $totals = $this->totalsFromItems($items, $source);

            $order = SalesOrder::query()->create([
                'company_id' => $source->company_id,
                'branch_id' => $source->branch_id,
                'customer_id' => $source->customer_id,
                'quotation_id' => null,
                'artwork_request_id' => $source->artwork_request_id,
                'inventory_item_id' => $source->inventory_item_id,
                'uses_existing_artwork' => $source->uses_existing_artwork,
                'customer_artwork_id' => $source->customer_artwork_id,
                'artwork_confirmed_by' => $source->customer_artwork_id ? $createdBy : null,
                'artwork_confirmed_at' => $source->customer_artwork_id ? now() : null,
                'order_number' => $this->numbering->next(DocumentType::SalesOrder, $source->company_id, $source->branch_id),
                'order_date' => now()->toDateString(),
                'required_date' => $overrides['required_date'] ?? $source->required_date,
                'status' => SalesOrderStatus::Confirmed,
                'subtotal' => $totals['subtotal'],
                'tax_amount' => $totals['tax_amount'],
                'discount_amount' => $totals['discount_amount'],
                'total_amount' => $totals['total_amount'],
                'is_direct_order' => true,
                'repeat_source_sales_order_id' => $source->id,
                'notes' => $overrides['notes'] ?? $source->notes,
                'created_by' => $createdBy,
            ]);

            foreach ($items as $index => $item) {
                $order->items()->create([
                    ...$item,
                    'sort_order' => $index + 1,
                ]);
            }

            $this->billingDefaults->applyToOrder($order, $source->customer);
            $this->communications->dispatch(DomainCommunicationEvent::SalesOrderCreated, $order->fresh(['items', 'customer']));

            return $order->fresh(['items', 'customer']);
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function createNewRun(Customer $customer, array $payload, int $createdBy): SalesOrder
    {
        return DB::transaction(function () use ($customer, $payload, $createdBy) {
            $items = $this->normalizeItems($payload);
            $totals = $this->totalsFromItems($items, null, $payload);

            $order = SalesOrder::query()->create([
                'company_id' => $customer->company_id,
                'branch_id' => $customer->branch_id,
                'customer_id' => $customer->id,
                'quotation_id' => null,
                'artwork_request_id' => $payload['artwork_request_id'] ?? null,
                'inventory_item_id' => $payload['inventory_item_id'] ?? null,
                'uses_existing_artwork' => (bool) ($payload['uses_existing_artwork'] ?? false),
                'customer_artwork_id' => $payload['customer_artwork_id'] ?? null,
                'artwork_confirmed_by' => ! empty($payload['customer_artwork_id']) ? $createdBy : null,
                'artwork_confirmed_at' => ! empty($payload['customer_artwork_id']) ? now() : null,
                'order_number' => $this->numbering->next(DocumentType::SalesOrder, $customer->company_id, $customer->branch_id),
                'order_date' => $payload['order_date'] ?? now()->toDateString(),
                'required_date' => $payload['required_date'] ?? null,
                'status' => SalesOrderStatus::Confirmed,
                'subtotal' => $totals['subtotal'],
                'tax_amount' => $totals['tax_amount'],
                'discount_amount' => $totals['discount_amount'],
                'total_amount' => $totals['total_amount'],
                'is_direct_order' => true,
                'created_by' => $createdBy,
                'notes' => $payload['notes'] ?? null,
            ]);

            foreach ($items as $index => $item) {
                $order->items()->create([
                    ...$item,
                    'sort_order' => $index + 1,
                ]);
            }

            $this->billingDefaults->applyToOrder($order, $customer);
            $this->communications->dispatch(DomainCommunicationEvent::SalesOrderCreated, $order->fresh(['items', 'customer']));

            return $order->fresh(['items', 'customer']);
        });
    }

    /**
     * @return list<array{item_name: string, description: ?string, quantity: float, unit_price: float, line_total: float}>
     */
    protected function buildRepeatItems(SalesOrder $source, ?float $quantityOverride): array
    {
        if ($source->items->isEmpty()) {
            return [[
                'item_name' => $source->inventoryItem?->item_name ?? __('Repeat order'),
                'description' => null,
                'quantity' => $quantityOverride ?? 1,
                'unit_price' => 0,
                'line_total' => 0,
            ]];
        }

        return $source->items->map(function ($item) use ($quantityOverride) {
            $quantity = $quantityOverride ?? (float) $item->quantity;
            $unitPrice = (float) $item->unit_price;

            return [
                'item_name' => $item->item_name,
                'description' => $item->description,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => round($quantity * $unitPrice, 2),
            ];
        })->all();
    }

    /**
     * @param  list<array{item_name: string, description: ?string, quantity: float, unit_price: float, line_total: float}>  $items
     * @param  array<string, mixed>  $payload
     * @return array{subtotal: float, tax_amount: float, discount_amount: float, total_amount: float}
     */
    protected function totalsFromItems(array $items, ?SalesOrder $source = null, array $payload = []): array
    {
        if ($items !== [] && ! isset($payload['subtotal'])) {
            $subtotal = collect($items)->sum('line_total');

            return [
                'subtotal' => round($subtotal, 2),
                'tax_amount' => round((float) ($source?->tax_amount ?? 0), 2),
                'discount_amount' => round((float) ($source?->discount_amount ?? 0), 2),
                'total_amount' => round($subtotal + (float) ($source?->tax_amount ?? 0) - (float) ($source?->discount_amount ?? 0), 2),
            ];
        }

        return [
            'subtotal' => round((float) ($payload['subtotal'] ?? $source?->subtotal ?? 0), 2),
            'tax_amount' => round((float) ($payload['tax_amount'] ?? $source?->tax_amount ?? 0), 2),
            'discount_amount' => round((float) ($payload['discount_amount'] ?? $source?->discount_amount ?? 0), 2),
            'total_amount' => round((float) ($payload['total_amount'] ?? $source?->total_amount ?? 0), 2),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array{item_name: string, description: ?string, quantity: float, unit_price: float, line_total: float}>
     */
    protected function normalizeItems(array $payload): array
    {
        if (! empty($payload['items']) && is_array($payload['items'])) {
            return collect($payload['items'])->map(function ($item, $index) {
                $quantity = (float) ($item['quantity'] ?? 1);
                $unitPrice = (float) ($item['unit_price'] ?? 0);

                return [
                    'item_name' => (string) ($item['item_name'] ?? __('Line :n', ['n' => $index + 1])),
                    'description' => $item['description'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => round((float) ($item['line_total'] ?? ($quantity * $unitPrice)), 2),
                ];
            })->all();
        }

        $quantity = (float) ($payload['quantity'] ?? 1);
        $unitPrice = (float) ($payload['unit_price'] ?? 0);
        $productName = $payload['item_name'] ?? null;

        if (! $productName && ! empty($payload['inventory_item_id'])) {
            $productName = \App\Models\Inventory\InventoryItem::query()
                ->find($payload['inventory_item_id'])?->item_name;
        }

        return [[
            'item_name' => (string) ($productName ?? __('Direct order line')),
            'description' => $payload['description'] ?? null,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => round($quantity * $unitPrice, 2),
        ]];
    }
}

<?php

namespace App\Support\Sales;

use App\Enums\SalesOrderStatus;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerArtwork;
use App\Models\Inventory\InventoryItem;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use App\Support\Crm\CustomerPrintSpecificationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CustomerOrderContextService
{
    public function __construct(
        protected CustomerPrintSpecificationService $printSpecifications,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildForDirectOrder(Customer $customer): array
    {
        try {
            $specs = $this->printSpecifications->selectableForOrderContext($customer);
        } catch (\Throwable $exception) {
            report($exception);
            $specs = [];
        }

        try {
            $summary = $this->printSpecifications->orderSelectionSummary($customer);
        } catch (\Throwable $exception) {
            report($exception);
            $summary = [
                'on_record' => count($specs),
                'selectable' => count($specs),
                'draft' => 0,
                'missing_product' => 0,
            ];
        }

        return [
            'customer_name' => $customer->company_name ?? $customer->name,
            'print_specifications' => $specs,
            'print_specification_summary' => $summary,
            'billing_defaults' => app(CustomerOrderBillingDefaultsService::class)->resolveForCustomer($customer),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function build(Customer $customer): array
    {
        return [
            'previous_orders' => $this->previousOrders($customer)->map(fn (SalesOrder $order) => $this->summarizeOrder($order)),
            'previous_jobs' => $this->previousJobs($customer)->map(fn (ProductionJobCard $job) => [
                'id' => $job->id,
                'job_card_number' => $job->job_card_number,
                'status' => $job->status->value,
                'product' => $job->inventoryItem?->item_name,
                'sales_order_id' => $job->sales_order_id,
                'sales_order' => $job->salesOrder?->order_number,
                'created_at' => $job->created_at?->toDateString(),
            ]),
            'artwork_library' => $this->artworkLibrary($customer)->map(fn (CustomerArtwork $art) => [
                'id' => $art->id,
                'artwork_name' => $art->artwork_name,
                'version' => $art->version_number,
                'customer_print_specification_id' => $art->customer_print_specification_id,
            ]),
            'print_specifications' => $this->printSpecifications->selectableForOrderContext($customer),
            'frequent_products' => $this->frequentlyOrderedProducts($customer),
            'serial_profiles' => $this->serialProfiles($customer),
            'billing_defaults' => app(CustomerOrderBillingDefaultsService::class)->resolveForCustomer($customer),
        ];
    }

    /**
     * @return Collection<int, SalesOrder>
     */
    public function previousOrders(Customer $customer, int $limit = 15): Collection
    {
        return SalesOrder::query()
            ->where('customer_id', $customer->id)
            ->whereNotIn('status', [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled])
            ->with([
                'inventoryItem:id,item_name,sku',
                'customerArtwork:id,artwork_name,version_number',
                'customerPrintSpecification:id,name,specification_code',
                'items',
                'jobCard:id,sales_order_id',
            ])
            ->latest('order_date')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, ProductionJobCard>
     */
    public function previousJobs(Customer $customer, int $limit = 10): Collection
    {
        return ProductionJobCard::query()
            ->where('customer_id', $customer->id)
            ->with(['inventoryItem:id,item_name,sku', 'salesOrder:id,order_number'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get(['id', 'job_card_number', 'customer_id', 'inventory_item_id', 'sales_order_id', 'status', 'created_at']);
    }

    /**
     * @return Collection<int, CustomerArtwork>
     */
    public function artworkLibrary(Customer $customer): Collection
    {
        return $customer->activeArtworks()->get();
    }

    /**
     * @return Collection<int, array{inventory_item_id: int, item_name: string, sku: ?string, order_count: int}>
     */
    public function frequentlyOrderedProducts(Customer $customer, int $limit = 8): Collection
    {
        $rows = SalesOrder::query()
            ->where('customer_id', $customer->id)
            ->whereNotNull('inventory_item_id')
            ->select('inventory_item_id', DB::raw('COUNT(*) as order_count'))
            ->groupBy('inventory_item_id')
            ->orderByDesc('order_count')
            ->limit($limit)
            ->get();

        $itemIds = $rows->pluck('inventory_item_id')->filter()->unique()->values();
        $items = InventoryItem::query()
            ->whereIn('id', $itemIds)
            ->get(['id', 'item_name', 'sku'])
            ->keyBy('id');

        return $rows->map(function ($row) use ($items) {
            $item = $items->get($row->inventory_item_id);

            return [
                'inventory_item_id' => (int) $row->inventory_item_id,
                'item_name' => $item?->item_name ?? __('Unknown'),
                'sku' => $item?->sku,
                'order_count' => (int) $row->order_count,
            ];
        });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function serialProfiles(Customer $customer): Collection
    {
        return $customer->productSerialProfiles()
            ->with('inventoryItem:id,item_name,sku')
            ->get()
            ->map(fn ($profile) => [
                'inventory_item_id' => $profile->inventory_item_id,
                'product' => $profile->inventoryItem?->item_name,
                'sku' => $profile->inventoryItem?->sku,
                'serial_prefix' => $profile->serial_prefix,
                'serial_padding_length' => $profile->serial_padding_length,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function orderSpecification(SalesOrder $order): array
    {
        $order->loadMissing(['items', 'inventoryItem', 'customerArtwork', 'jobCard']);

        $firstItem = $order->items->first();

        return [
            ...$this->summarizeOrder($order),
            'quantity' => (float) ($firstItem?->quantity ?? 1),
            'unit_price' => (float) ($firstItem?->unit_price ?? 0),
            'item_name' => $firstItem?->item_name ?? $order->inventoryItem?->item_name,
            'description' => $firstItem?->description,
            'items' => $order->items->map(fn ($item) => [
                'item_name' => $item->item_name,
                'description' => $item->description,
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'line_total' => (float) $item->line_total,
            ])->values()->all(),
            'has_production_route' => (bool) $order->jobCard,
            'notes' => $order->notes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function summarizeOrder(SalesOrder $order): array
    {
        $firstItem = $order->items->first();

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'order_date' => $order->order_date?->toDateString(),
            'required_date' => $order->required_date?->toDateString(),
            'status' => $order->status->value,
            'customer_print_specification_id' => $order->customer_print_specification_id,
            'print_specification' => $order->customerPrintSpecification?->name,
            'inventory_item_id' => $order->inventory_item_id,
            'product' => $order->inventoryItem?->item_name,
            'sku' => $order->inventoryItem?->sku,
            'uses_existing_artwork' => (bool) $order->uses_existing_artwork,
            'customer_artwork_id' => $order->customer_artwork_id,
            'artwork' => $order->customerArtwork?->artwork_name,
            'artwork_version' => $order->customerArtwork?->version_number,
            'quantity' => (float) ($firstItem?->quantity ?? 0),
            'unit_price' => (float) ($firstItem?->unit_price ?? 0),
            'total_amount' => (float) $order->total_amount,
            'is_direct_order' => (bool) $order->is_direct_order,
        ];
    }
}

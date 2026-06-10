<?php

namespace App\Support\Export;

use App\Enums\StockIssueDestination;
use App\Http\Controllers\Admin\Concerns\ExportsTabularIndex;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Models\Inventory\Brand;
use App\Models\Inventory\CycleCountSchedule;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\InventorySubcategory;
use App\Models\Inventory\ItemAttribute;
use App\Models\Inventory\PriceList;
use App\Models\Inventory\StockAdjustment;
use App\Models\Inventory\StockCount;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\StockReceipt;
use App\Models\Inventory\Warehouse;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\Rfq;
use App\Models\Procurement\SupplierQuotation;
use App\Models\Procurement\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupplyChainListingExporter
{
    use ExportsTabularIndex;
    use ScopesToTenant;

    /** @var list<string> */
    protected array $inventoryListings = [
        'items',
        'categories',
        'subcategories',
        'brands',
        'attributes',
        'price-lists',
        'warehouses',
        'store-balances',
        'stock-receipts',
        'stock-issues',
        'transfers',
        'adjustments',
        'movements',
        'stock-counts',
        'cycle-counts',
    ];

    /** @var list<string> */
    protected array $procurementListings = [
        'vendors',
        'purchase-requests',
        'purchase-orders',
        'goods-receipts',
        'supplier-quotations',
        'rfqs',
    ];

    public function downloadInventory(
        string $listing,
        string $format,
        TabularExportWriter $writer,
        Request $request,
    ): StreamedResponse {
        abort_unless(in_array($listing, $this->inventoryListings, true), 404);

        [$headers, $rows, $basename, $title] = match ($listing) {
            'items' => $this->inventoryItems(),
            'categories' => $this->inventoryCategories(),
            'subcategories' => $this->inventorySubcategories(),
            'brands' => $this->inventoryBrands(),
            'attributes' => $this->inventoryAttributes(),
            'price-lists' => $this->inventoryPriceLists(),
            'warehouses' => $this->inventoryWarehouses(),
            'store-balances' => $this->inventoryStoreBalances(),
            'stock-receipts' => $this->inventoryStockReceipts(),
            'stock-issues' => $this->inventoryStockIssues(),
            'transfers' => $this->inventoryTransfers(),
            'adjustments' => $this->inventoryAdjustments(),
            'movements' => $this->inventoryMovements(),
            'stock-counts' => $this->inventoryStockCounts(),
            'cycle-counts' => $this->inventoryCycleCounts(),
            default => abort(404),
        };

        return $this->downloadTabularExport($writer, $format, $basename, $headers, $rows, $title);
    }

    public function downloadProcurement(
        string $listing,
        string $format,
        TabularExportWriter $writer,
        Request $request,
    ): StreamedResponse {
        abort_unless(in_array($listing, $this->procurementListings, true), 404);

        [$headers, $rows, $basename, $title] = match ($listing) {
            'vendors' => $this->procurementVendors(),
            'purchase-requests' => $this->procurementPurchaseRequests(),
            'purchase-orders' => $this->procurementPurchaseOrders(),
            'goods-receipts' => $this->procurementGoodsReceipts(),
            'supplier-quotations' => $this->procurementSupplierQuotations(),
            'rfqs' => $this->procurementRfqs(),
            default => abort(404),
        };

        return $this->downloadTabularExport($writer, $format, $basename, $headers, $rows, $title);
    }

    /**
     * @return array{0: list<string>, 1: list<list<string>>, 2: string, 3: string}
     */
    protected function inventoryItems(): array
    {
        $this->authorizeListing(InventoryItem::class, 'viewAny');

        $items = $this->scopeToTenant(
            InventoryItem::query()->with(['category', 'subcategory', 'brand', 'images'])
        )->orderBy('item_name')->get();

        $headers = [__('Item'), __('SKU'), __('Category'), __('Brand'), __('Image'), __('Reorder')];
        $rows = $items->map(fn (InventoryItem $item) => [
            $item->item_name,
            $item->sku,
            $item->category?->name ?? '—',
            $item->brand?->name ?? '—',
            $item->images->isNotEmpty() ? __('Yes') : __('Missing'),
            (string) $item->reorder_level,
        ])->all();

        return [$headers, $rows, 'inventory-items', __('Inventory items')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string>>, 2: string, 3: string}
     */
    protected function inventoryCategories(): array
    {
        abort_unless(auth()->user()?->can('catalogue.view'), 403);

        $categories = InventoryCategory::query()
            ->forTenant()
            ->with(['defaultUom'])
            ->withCount(['items', 'subcategories'])
            ->orderBy('name')
            ->get();

        $headers = [__('Category'), __('Code'), __('Default UOM'), __('Reorder'), __('Items'), __('Subcategories')];
        $rows = $categories->map(fn (InventoryCategory $category) => [
            $category->name,
            $category->code,
            $category->defaultUom?->name ?? '—',
            Str::headline($category->reorder_behavior),
            (string) $category->items_count,
            (string) $category->subcategories_count,
        ])->all();

        return [$headers, $rows, 'catalogue-categories', __('Categories')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string>>, 2: string, 3: string}
     */
    protected function inventorySubcategories(): array
    {
        abort_unless(auth()->user()?->can('catalogue.view'), 403);

        $subcategories = InventorySubcategory::query()
            ->forTenant()
            ->with('category')
            ->withCount('items')
            ->orderBy('name')
            ->get();

        $headers = [__('Subcategory'), __('Code'), __('Category'), __('Items')];
        $rows = $subcategories->map(fn (InventorySubcategory $subcategory) => [
            $subcategory->name,
            $subcategory->code,
            $subcategory->category?->name ?? '—',
            (string) $subcategory->items_count,
        ])->all();

        return [$headers, $rows, 'catalogue-subcategories', __('Subcategories')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string>>, 2: string, 3: string}
     */
    protected function inventoryBrands(): array
    {
        abort_unless(auth()->user()?->can('catalogue.view'), 403);

        $brands = Brand::query()->forTenant()->withCount('items')->orderBy('name')->get();

        $headers = [__('Brand'), __('Code'), __('Logo'), __('Items')];
        $rows = $brands->map(fn (Brand $brand) => [
            $brand->name,
            $brand->code,
            filled($brand->logo_path) ? __('Yes') : '—',
            (string) $brand->items_count,
        ])->all();

        return [$headers, $rows, 'catalogue-brands', __('Brands')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string>>, 2: string, 3: string}
     */
    protected function inventoryAttributes(): array
    {
        abort_unless(auth()->user()?->can('catalogue.view'), 403);

        $attributes = ItemAttribute::query()->forTenant()->orderBy('name')->get();

        $headers = [__('Attribute'), __('Code'), __('Type'), __('Required')];
        $rows = $attributes->map(fn (ItemAttribute $attribute) => [
            $attribute->name,
            $attribute->code,
            Str::headline($attribute->data_type ?? ''),
            $attribute->is_required ? __('Yes') : __('No'),
        ])->all();

        return [$headers, $rows, 'catalogue-attributes', __('Attributes')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string>>, 2: string, 3: string}
     */
    protected function inventoryPriceLists(): array
    {
        abort_unless(auth()->user()?->can('catalogue.view'), 403);

        $priceLists = PriceList::query()->forTenant()->withCount('items')->orderBy('name')->get();

        $headers = [__('Name'), __('Currency'), __('Effective'), __('Status'), __('Items')];
        $rows = $priceLists->map(fn (PriceList $list) => [
            $list->name,
            $list->currency,
            $list->effective_date?->toDateString() ?? '—',
            Str::headline((string) $list->status),
            (string) $list->items_count,
        ])->all();

        return [$headers, $rows, 'price-lists', __('Price lists')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string>>, 2: string, 3: string}
     */
    protected function inventoryWarehouses(): array
    {
        $this->authorizeListing(Warehouse::class, 'viewAny');

        $warehouses = $this->scopeToTenant(
            Warehouse::query()->with(['branch'])->withCount('managers')
        )->orderBy('name')->get();

        $headers = [__('Warehouse'), __('Code'), __('Branch'), __('Managers'), __('Status')];
        $rows = $warehouses->map(fn (Warehouse $warehouse) => [
            $warehouse->name,
            $warehouse->code,
            $warehouse->branch?->name ?? '—',
            (string) $warehouse->managers_count,
            $warehouse->is_active ? __('Active') : __('Inactive'),
        ])->all();

        return [$headers, $rows, 'warehouses', __('Warehouses')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string>>, 2: string, 3: string}
     */
    protected function inventoryStoreBalances(): array
    {
        $this->authorizeListing(Warehouse::class, 'viewAny');

        $warehouses = Warehouse::query()->forTenant()->orderBy('name')->get();
        $items = InventoryItem::query()->forTenant()->with('category')->where('is_active', true)->orderBy('item_name')->get();

        $movementMap = InventoryMovement::query()
            ->forTenant()
            ->selectRaw('warehouse_id, inventory_item_id, SUM(quantity) as balance')
            ->groupBy('warehouse_id', 'inventory_item_id')
            ->get()
            ->keyBy(fn ($movement) => "{$movement->warehouse_id}:{$movement->inventory_item_id}");

        $headers = [__('Warehouse'), __('SKU'), __('Item'), __('Category'), __('Balance'), __('Reorder')];
        $rows = [];

        foreach ($warehouses as $warehouse) {
            foreach ($items as $item) {
                $movement = $movementMap->get("{$warehouse->id}:{$item->id}");
                $balance = (float) ($movement?->balance ?? 0);

                if ($balance === 0.0) {
                    continue;
                }

                $rows[] = [
                    $warehouse->name,
                    $item->sku,
                    $item->item_name,
                    $item->category?->name ?? '—',
                    (string) $balance,
                    (string) $item->reorder_level,
                ];
            }
        }

        return [$headers, $rows, 'store-balances', __('Store balances')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string>>, 2: string, 3: string}
     */
    protected function inventoryStockReceipts(): array
    {
        $this->authorizeListing(StockReceipt::class, 'viewAny');

        $receipts = $this->scopeToTenant(StockReceipt::query())->latest()->get();

        $headers = [__('Receipt'), __('Status')];
        $rows = $receipts->map(fn (StockReceipt $receipt) => [
            $receipt->receipt_number,
            Str::headline($receipt->status->value),
        ])->all();

        return [$headers, $rows, 'stock-receipts', __('Stock receipts')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string>>, 2: string, 3: string}
     */
    protected function inventoryStockIssues(): array
    {
        $this->authorizeListing(StockIssue::class, 'viewAny');

        $issues = $this->scopeToTenant(StockIssue::query())->latest()->get();

        $headers = [__('Issue'), __('Status')];
        $rows = $issues->map(fn (StockIssue $issue) => [
            $issue->issue_number,
            Str::headline($issue->status->value),
        ])->all();

        return [$headers, $rows, 'stock-issues', __('Stock issues')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string>>, 2: string, 3: string}
     */
    protected function inventoryTransfers(): array
    {
        $this->authorizeListing(StockIssue::class, 'viewAny');

        $transfers = $this->scopeToTenant(
            StockIssue::query()
                ->with(['warehouse', 'toWarehouse'])
                ->where('destination', StockIssueDestination::Transfer)
                ->latest('issue_date')
        )->get();

        $headers = [__('Transfer'), __('From'), __('To'), __('Date'), __('Status')];
        $rows = $transfers->map(fn (StockIssue $transfer) => [
            $transfer->issue_number,
            $transfer->warehouse?->name ?? '—',
            $transfer->toWarehouse?->name ?? '—',
            $transfer->issue_date?->format('Y-m-d') ?? '—',
            Str::headline($transfer->status->value),
        ])->all();

        return [$headers, $rows, 'store-transfers', __('Store transfers')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string>>, 2: string, 3: string}
     */
    protected function inventoryAdjustments(): array
    {
        $this->authorizeListing(StockAdjustment::class, 'viewAny');

        $adjustments = $this->scopeToTenant(StockAdjustment::query())->latest()->get();

        $headers = [__('Adjustment'), __('Status')];
        $rows = $adjustments->map(fn (StockAdjustment $adjustment) => [
            $adjustment->adjustment_number,
            Str::headline($adjustment->status->value),
        ])->all();

        return [$headers, $rows, 'stock-adjustments', __('Stock adjustments')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string>>, 2: string, 3: string}
     */
    protected function inventoryMovements(): array
    {
        $this->authorizeListing(InventoryMovement::class, 'viewAny');

        $movements = $this->scopeToTenant(
            InventoryMovement::query()->with(['item', 'warehouse'])->latest('created_at')
        )->get();

        $headers = [__('Date'), __('Item'), __('Warehouse'), __('Type'), __('Qty')];
        $rows = $movements->map(fn (InventoryMovement $movement) => [
            $movement->movement_date->format('Y-m-d'),
            $movement->item?->sku ?? '—',
            $movement->warehouse?->name ?? '—',
            Str::headline($movement->movement_type->value),
            (string) $movement->quantity,
        ])->all();

        return [$headers, $rows, 'inventory-movements', __('Inventory movements')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string>>, 2: string, 3: string}
     */
    protected function inventoryStockCounts(): array
    {
        $this->authorizeListing(StockCount::class, 'viewAny');

        $counts = $this->scopeToTenant(
            StockCount::query()->with(['warehouse'])->latest('count_date')
        )->get();

        $headers = [__('Count'), __('Warehouse'), __('Date'), __('Status')];
        $rows = $counts->map(fn (StockCount $count) => [
            $count->count_number,
            $count->warehouse?->name ?? '—',
            $count->count_date?->format('Y-m-d') ?? '—',
            Str::headline($count->status->value),
        ])->all();

        return [$headers, $rows, 'stock-counts', __('Stock counts')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string>>, 2: string, 3: string}
     */
    protected function inventoryCycleCounts(): array
    {
        $this->authorizeListing(CycleCountSchedule::class, 'viewAny');

        $schedules = $this->scopeToTenant(
            CycleCountSchedule::query()->with(['warehouse', 'category', 'responsibleUser'])->latest()
        )->get();

        $headers = [__('Warehouse'), __('Category'), __('Frequency'), __('Next count'), __('Responsible'), __('Status')];
        $rows = $schedules->map(fn (CycleCountSchedule $schedule) => [
            $schedule->warehouse?->name ?? '—',
            $schedule->category?->name ?? '—',
            Str::headline($schedule->frequency->value),
            $schedule->next_count_date?->format('Y-m-d') ?? '—',
            $schedule->responsibleUser?->name ?? '—',
            Str::headline($schedule->status->value),
        ])->all();

        return [$headers, $rows, 'cycle-count-schedules', __('Cycle count schedules')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string>>, 2: string, 3: string}
     */
    protected function procurementVendors(): array
    {
        $this->authorizeListing(Vendor::class, 'viewAny');

        $vendors = $this->scopeToTenant(Vendor::query()->latest('vendor_name'))->get();

        $headers = [__('Vendor'), __('Code'), __('Type'), __('Status')];
        $rows = $vendors->map(fn (Vendor $vendor) => [
            $vendor->vendor_name,
            $vendor->vendor_code,
            Str::headline($vendor->vendor_type->value),
            Str::headline($vendor->status->value),
        ])->all();

        return [$headers, $rows, 'vendors', __('Vendors')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string>>, 2: string, 3: string}
     */
    protected function procurementPurchaseRequests(): array
    {
        $this->authorizeListing(PurchaseRequest::class, 'viewAny');

        $requests = $this->scopeToTenant(PurchaseRequest::query()->latest())->get();

        $headers = [__('Request'), __('Status')];
        $rows = $requests->map(fn (PurchaseRequest $request) => [
            $request->request_number,
            Str::headline($request->status->value),
        ])->all();

        return [$headers, $rows, 'purchase-requests', __('Purchase requests')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string>>, 2: string, 3: string}
     */
    protected function procurementPurchaseOrders(): array
    {
        $this->authorizeListing(PurchaseOrder::class, 'viewAny');

        $orders = $this->scopeToTenant(
            PurchaseOrder::query()->with(['vendor'])->latest('order_date')
        )->get();

        $headers = [__('PO Number'), __('Vendor'), __('Date'), __('Total'), __('Status')];
        $rows = $orders->map(fn (PurchaseOrder $order) => [
            $order->po_number,
            $order->vendor?->vendor_name ?? '—',
            $order->order_date?->format('Y-m-d') ?? '—',
            (string) $order->total_amount,
            Str::headline($order->status->value),
        ])->all();

        return [$headers, $rows, 'purchase-orders', __('Purchase orders')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string>>, 2: string, 3: string}
     */
    protected function procurementGoodsReceipts(): array
    {
        $this->authorizeListing(GoodsReceipt::class, 'viewAny');

        $receipts = $this->scopeToTenant(GoodsReceipt::query()->with(['vendor'])->latest())->get();

        $headers = [__('Receipt'), __('Vendor'), __('Status')];
        $rows = $receipts->map(fn (GoodsReceipt $receipt) => [
            $receipt->receipt_number,
            $receipt->vendor?->vendor_name ?? '—',
            Str::headline($receipt->status->value),
        ])->all();

        return [$headers, $rows, 'goods-receipts', __('Goods receipts')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string>>, 2: string, 3: string}
     */
    protected function procurementSupplierQuotations(): array
    {
        $this->authorizeListing(SupplierQuotation::class, 'viewAny');

        $quotations = $this->scopeToTenant(
            SupplierQuotation::query()->with(['vendor'])->latest()
        )->get();

        $headers = [__('Quotation'), __('Vendor'), __('Status')];
        $rows = $quotations->map(fn (SupplierQuotation $quotation) => [
            $quotation->quotation_number,
            $quotation->vendor?->vendor_name ?? '—',
            Str::headline($quotation->status->value),
        ])->all();

        return [$headers, $rows, 'supplier-quotations', __('Supplier quotations')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string>>, 2: string, 3: string}
     */
    protected function procurementRfqs(): array
    {
        $this->authorizeListing(Rfq::class, 'viewAny');

        $rfqs = $this->scopeToTenant(Rfq::query()->latest())->get();

        $headers = [__('RFQ'), __('Status')];
        $rows = $rfqs->map(fn (Rfq $rfq) => [
            $rfq->rfq_number,
            Str::headline($rfq->status->value),
        ])->all();

        return [$headers, $rows, 'rfqs', __('RFQs')];
    }

    /**
     * @param  class-string  $modelClass
     */
    protected function authorizeListing(string $modelClass, string $ability): void
    {
        Gate::authorize($ability, $modelClass);
    }
}

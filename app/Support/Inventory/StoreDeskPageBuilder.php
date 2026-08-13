<?php

namespace App\Support\Inventory;

use App\Enums\InventoryDocumentStatus;
use App\Enums\InventoryStockRole;
use App\Enums\StockIssueDestination;
use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\StockAdjustment;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\StockReceipt;
use App\Models\Inventory\Warehouse;
use App\Support\Inventory\ReorderAlertService;
use Illuminate\Http\Request;

class StoreDeskPageBuilder
{
    use ResolvesInventoryTenant;

    public function __construct(
        protected StoreDeskService $desk,
        protected StoreDeskWorkQueueService $workQueue,
        protected ReorderAlertService $reorderAlerts,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Request $request): array
    {
        $user = $request->user();
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();
        $activeView = StoreDeskViews::normalize($request->query('view'));

        $payload = [
            'operatorMode' => $user?->prefersStorekeeperOperatorMode() ?? false,
            'activeStoreView' => $activeView,
            'fullSupplyChainDeskUrl' => route('admin.workspaces.supply-chain', ['desk' => 1]),
            'catalogueUrl' => route('admin.store.desk.catalogue'),
            'reorderAlertsUrl' => route('admin.store.desk.reorder-alerts'),
            'searchUrl' => route('admin.store.desk.items.search'),
        ];

        if (StoreDeskViews::isPanelView($activeView)) {
            return array_merge($payload, $this->panelPayload($activeView, $request));
        }

        $workQueue = $this->workQueue->present($request);

        return array_merge($payload, [
            'workQueue' => $workQueue,
            'fastActions' => $this->desk->fastActions($user),
            'movementFeed' => $this->desk->movementFeed(),
            'lowStockItems' => $this->desk->lowStockPriority(),
            'warehouseSnapshot' => $this->desk->warehouseSnapshot($companyId, $branchId),
            'receivingPipeline' => $this->desk->receivingPipeline($user),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function panelPayload(string $view, Request $request): array
    {
        if (StoreDeskViews::isInlineRegister($view)) {
            return $this->registerPayload($view, $request);
        }

        return match ($view) {
            StoreDeskViews::PRODUCTS => $this->productsPayload($request),
            StoreDeskViews::BALANCES => $this->balancesPayload(),
            StoreDeskViews::MOVEMENTS => $this->movementsPayload($request),
            StoreDeskViews::ALERTS => $this->alertsPayload($request),
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function productsPayload(Request $request): array
    {
        $search = trim((string) $request->query('search', ''));
        $stockRole = $request->string('stock_role')->toString() ?: null;

        $items = InventoryItem::query()
            ->forTenant()
            ->with(['category', 'brand', 'unitOfMeasure'])
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.strtolower($search).'%';
                $query->where(function ($inner) use ($like) {
                    $inner->whereRaw('LOWER(sku) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(item_name) LIKE ?', [$like]);
                });
            })
            ->when($stockRole !== null && $stockRole !== '' && $stockRole !== 'all', function ($query) use ($stockRole) {
                $query->where('stock_role', $stockRole);
            })
            ->orderBy('item_name')
            ->paginate(20)
            ->withQueryString();

        return [
            'registerTitle' => __('Products'),
            'registerDescription' => __('Every inventory item and its stock role. Use this list when production needs an item classified as a finished good.'),
            'registerCreateUrl' => route('admin.inventory.items.create', ['from' => 'store-desk']),
            'registerCreateLabel' => __('New item'),
            'registerCanCreate' => $request->user()?->can('create', InventoryItem::class) ?? false,
            'registerCreateModal' => true,
            'items' => $items,
            'stockRole' => $stockRole ?: 'all',
            'stockRoles' => InventoryStockRole::cases(),
            'search' => $search,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function balancesPayload(): array
    {
        $warehouses = Warehouse::query()->forTenant()->orderBy('name')->get();
        $items = InventoryItem::query()->forTenant()->with('category')->where('is_active', true)->orderBy('item_name')->get();
        $categories = InventoryCategory::query()->forTenant()->orderBy('name')->get();

        $movementMap = InventoryMovement::query()
            ->forTenant()
            ->selectRaw('warehouse_id, inventory_item_id, SUM(quantity) as balance')
            ->groupBy('warehouse_id', 'inventory_item_id')
            ->get()
            ->keyBy(fn ($movement) => "{$movement->warehouse_id}:{$movement->inventory_item_id}");

        $balances = $warehouses->flatMap(fn (Warehouse $warehouse) => $items->map(function (InventoryItem $item) use ($warehouse, $movementMap) {
            $movement = $movementMap->get("{$warehouse->id}:{$item->id}");

            return (object) [
                'item_id' => $item->id,
                'item_public_id' => $item->public_id,
                'warehouse_id' => $warehouse->id,
                'warehouse_public_id' => $warehouse->public_id,
                'warehouse_code' => $warehouse->code,
                'warehouse_name' => $warehouse->name,
                'sku' => $item->sku,
                'item_name' => $item->item_name,
                'reorder_level' => $item->reorder_level,
                'standard_cost' => $item->standard_cost,
                'category_name' => $item->category?->name,
                'balance' => (float) ($movement?->balance ?? 0),
            ];
        }));

        return [
            'registerTitle' => __('Store Balances'),
            'registerDescription' => __('View stock position by item, warehouse, and branch.'),
            'balances' => $balances,
            'warehouses' => $warehouses,
            'categories' => $categories,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function movementsPayload(Request $request): array
    {
        $warehouseId = $request->integer('warehouse_id') ?: null;

        $query = InventoryMovement::query()
            ->forTenant()
            ->with(['item', 'warehouse', 'creator'])
            ->latest('created_at');

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return [
            'registerTitle' => __('Inventory movements'),
            'registerDescription' => __('Audit trail — source of stock truth.'),
            'movements' => $query->paginate(20)->withQueryString(),
            'warehouse' => $warehouseId ? Warehouse::query()->find($warehouseId) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function alertsPayload(Request $request): array
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        $filters = $request->only(['warehouse_id', 'category_id', 'subcategory_id', 'status', 'critical_only', 'search']);

        return [
            'registerTitle' => __('Reorder Alerts'),
            'registerDescription' => __('Actionable low-stock alerts with acknowledgement, resolution, and purchase request handoff.'),
            'alerts' => $this->reorderAlerts->paginate($companyId, $branchId, $filters),
            'filters' => $filters,
            'warehouses' => Warehouse::query()->forTenant()->orderBy('name')->get(),
            'categories' => InventoryCategory::query()->forTenant()->orderBy('name')->get(),
            'statuses' => \App\Enums\ReorderAlertStatus::cases(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function registerPayload(string $view, Request $request): array
    {
        $perPage = (int) config('platform.pagination.default', 15);

        return match ($view) {
            StoreDeskViews::RECEIPTS => [
                'registerTitle' => __('Stock receipts'),
                'registerCreateUrl' => route('admin.inventory.receipts.create', ['from' => 'store-desk']),
                'registerCreateLabel' => __('New receipt'),
                'registerCanCreate' => $request->user()?->can('create', StockReceipt::class) ?? false,
                'registerCreateModal' => true,
                'receipts' => StockReceipt::query()
                    ->forTenant()
                    ->with(['warehouse', 'receiver'])
                    ->latest('receipt_date')
                    ->paginate($perPage)
                    ->withQueryString(),
            ],
            StoreDeskViews::ISSUES => [
                'registerTitle' => __('Stock issues'),
                'registerCreateUrl' => route('admin.inventory.issues.create', ['from' => 'store-desk']),
                'registerCreateLabel' => __('New stock issue'),
                'registerCanCreate' => $request->user()?->can('inventory.issue') ?? false,
                'registerCreateModal' => true,
                'issues' => StockIssue::query()
                    ->forTenant()
                    ->with(['warehouse', 'issuer'])
                    ->latest('issue_date')
                    ->paginate($perPage)
                    ->withQueryString(),
            ],
            StoreDeskViews::TRANSFERS => [
                'registerTitle' => __('Store Transfers'),
                'registerDescription' => __('Move stock between warehouses using controlled inventory movements.'),
                'registerCreateUrl' => route('admin.inventory.transfers.create', ['from' => 'store-desk']),
                'registerCreateLabel' => __('New transfer'),
                'registerCanCreate' => $request->user()?->can('inventory.transfer') ?? false,
                'registerCreateModal' => true,
                'transfers' => $this->transfersQuery($request)->paginate($perPage)->withQueryString(),
                'warehouses' => Warehouse::query()->forTenant()->orderBy('name')->get(),
                'statuses' => InventoryDocumentStatus::cases(),
            ],
            StoreDeskViews::ADJUSTMENTS => [
                'registerTitle' => __('Stock adjustments'),
                'registerCreateUrl' => route('admin.inventory.adjustments.create', ['from' => 'store-desk']),
                'registerCreateLabel' => __('New adjustment'),
                'registerCanCreate' => $request->user()?->can('create', StockAdjustment::class) ?? false,
                'registerCreateModal' => true,
                'adjustments' => StockAdjustment::query()
                    ->forTenant()
                    ->with(['warehouse', 'adjuster'])
                    ->latest('adjustment_date')
                    ->paginate($perPage)
                    ->withQueryString(),
            ],
            default => [],
        };
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Inventory\StockIssue>
     */
    protected function transfersQuery(Request $request)
    {
        $query = StockIssue::query()
            ->forTenant()
            ->with(['warehouse', 'toWarehouse', 'issuer'])
            ->where('destination', StockIssueDestination::Transfer)
            ->latest('issue_date');

        if ($warehouseId = $request->integer('warehouse_id')) {
            $query->where(fn ($q) => $q->where('warehouse_id', $warehouseId)->orWhere('to_warehouse_id', $warehouseId));
        }

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    public function catalogue(Request $request): array
    {
        $this->authorizeCatalogue($request);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();
        $search = trim((string) $request->query('search', ''));

        $items = InventoryItem::query()
            ->with(['category', 'brand', 'unitOfMeasure'])
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.strtolower($search).'%';
                $query->where(function ($inner) use ($like) {
                    $inner->whereRaw('LOWER(sku) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(item_name) LIKE ?', [$like]);
                });
            })
            ->orderBy('item_name')
            ->paginate(20)
            ->withQueryString();

        return [
            'items' => $items,
            'rows' => $this->desk->catalogueRows(collect($items->items()), $companyId, $branchId),
            'search' => $search,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function reorderAlerts(Request $request): array
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        $filters = array_merge(
            ['status' => \App\Enums\ReorderAlertStatus::Open->value],
            $request->only(['search']),
        );

        return [
            'alerts' => $this->reorderAlerts->paginate($companyId, $branchId, $filters),
            'search' => $filters['search'] ?? '',
        ];
    }

    protected function authorizeCatalogue(Request $request): void
    {
        abort_unless($request->user()?->can('viewAny', InventoryItem::class), 403);
    }
}

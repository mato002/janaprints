<?php

namespace App\Support\Inventory;

use App\Enums\InventoryDocumentStatus;
use App\Enums\StockIssueDestination;
use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Models\Inventory\InventoryItem;
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

        if (StoreDeskViews::isInlineRegister($activeView)) {
            return array_merge($payload, $this->registerPayload($activeView, $request));
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

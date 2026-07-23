<?php

namespace App\Support\Inventory;

use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryReorderAlert;
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

        $workQueue = $this->workQueue->present($request);

        return [
            'operatorMode' => $user?->prefersStorekeeperOperatorMode() ?? false,
            'workQueue' => $workQueue,
            'fastActions' => $this->desk->fastActions($user),
            'movementFeed' => $this->desk->movementFeed(),
            'lowStockItems' => $this->desk->lowStockPriority(),
            'warehouseSnapshot' => $this->desk->warehouseSnapshot($companyId, $branchId),
            'receivingPipeline' => $this->desk->receivingPipeline($user),
            'searchUrl' => route('admin.store.desk.items.search'),
            'fullSupplyChainDeskUrl' => route('admin.workspaces.supply-chain', ['desk' => 1]),
            'catalogueUrl' => route('admin.store.desk.catalogue'),
            'reorderAlertsUrl' => route('admin.store.desk.reorder-alerts'),
        ];
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

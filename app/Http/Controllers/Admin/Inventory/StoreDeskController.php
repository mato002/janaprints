<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Enums\InventoryDocumentStatus;
use App\Enums\ReorderAlertStatus;
use App\Enums\StockCountStatus;
use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\InventoryReorderAlert;
use App\Models\Inventory\StockCount;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\StockReceipt;
use App\Support\Inventory\ReorderAlertService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreDeskController extends Controller
{
    use ResolvesInventoryTenant;

    public function __construct(
        protected ReorderAlertService $reorderAlerts,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', InventoryItem::class);

        $draftReceipts = StockReceipt::query()
            ->with(['warehouse', 'receiver'])
            ->where('status', InventoryDocumentStatus::Draft)
            ->latest('receipt_date')
            ->limit(20)
            ->get();

        $draftIssues = StockIssue::query()
            ->with(['warehouse', 'issuer'])
            ->where('status', InventoryDocumentStatus::Draft)
            ->latest('issue_date')
            ->limit(20)
            ->get();

        $openCounts = StockCount::query()
            ->with(['warehouse', 'creator'])
            ->whereIn('status', [
                StockCountStatus::Draft,
                StockCountStatus::InProgress,
                StockCountStatus::Submitted,
                StockCountStatus::Approved,
            ])
            ->latest('count_date')
            ->limit(20)
            ->get();

        $lowStockAlerts = InventoryReorderAlert::where('status', '!=', ReorderAlertStatus::Resolved)->count();
        $openStockCounts = $openCounts->count();
        $totalItems = InventoryItem::count();

        $recentMovements = InventoryMovement::with(['item', 'warehouse', 'creator'])
            ->latest('created_at')
            ->limit(10)
            ->get();

        $operatorMode = $request->user()?->prefersStorekeeperOperatorMode() ?? false;

        return view('admin.store.desk.index', [
            'operatorMode' => $operatorMode,
            'draftReceipts' => $draftReceipts,
            'draftIssues' => $draftIssues,
            'openCounts' => $openCounts,
            'pendingReceipts' => $draftReceipts->count(),
            'pendingIssues' => $draftIssues->count(),
            'lowStockAlerts' => $lowStockAlerts,
            'openStockCounts' => $openStockCounts,
            'totalItems' => $totalItems,
            'recentMovements' => $recentMovements,
            'fullSupplyChainDeskUrl' => route('admin.workspaces.supply-chain', ['desk' => 1]),
            'catalogueUrl' => route('admin.store.desk.catalogue'),
            'reorderAlertsUrl' => route('admin.store.desk.reorder-alerts'),
        ]);
    }

    public function catalogue(Request $request): View
    {
        $this->authorize('viewAny', InventoryItem::class);

        $search = trim((string) $request->query('search', ''));

        $items = InventoryItem::query()
            ->with(['category', 'brand'])
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

        return view('admin.store.desk.catalogue-modal', [
            'items' => $items,
            'search' => $search,
        ]);
    }

    public function reorderAlerts(Request $request): View
    {
        $this->authorize('viewAny', InventoryReorderAlert::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        $filters = array_merge(
            ['status' => ReorderAlertStatus::Open->value],
            $request->only(['search']),
        );

        $alerts = $this->reorderAlerts->paginate($companyId, $branchId, $filters);

        return view('admin.store.desk.reorder-alerts-modal', [
            'alerts' => $alerts,
            'search' => $filters['search'] ?? '',
        ]);
    }
}

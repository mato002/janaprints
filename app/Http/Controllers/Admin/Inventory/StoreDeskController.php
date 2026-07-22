<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryReorderAlert;
use App\Support\Inventory\StoreDeskPageBuilder;
use App\Support\Inventory\StoreDeskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreDeskController extends Controller
{
    use ResolvesInventoryTenant;

    public function __construct(
        protected StoreDeskPageBuilder $pageBuilder,
        protected StoreDeskService $desk,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', InventoryItem::class);

        return view('admin.store.desk.index', $this->pageBuilder->build($request));
    }

    public function searchItems(Request $request): JsonResponse
    {
        $this->authorize('viewAny', InventoryItem::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        $results = $this->desk->searchItems(
            (string) $request->query('q', ''),
            $companyId,
            $branchId,
        );

        return response()->json(['results' => $results]);
    }

    public function catalogue(Request $request): View
    {
        $this->authorize('viewAny', InventoryItem::class);

        $data = $this->pageBuilder->catalogue($request);

        return view('admin.store.desk.catalogue-modal', $data);
    }

    public function reorderAlerts(Request $request): View
    {
        $this->authorize('viewAny', InventoryReorderAlert::class);

        return view('admin.store.desk.reorder-alerts-modal', $this->pageBuilder->reorderAlerts($request));
    }
}

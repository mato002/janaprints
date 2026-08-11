<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Crm\Customer;
use App\Models\Sales\SalesOrder;
use App\Support\Production\MaterialReadinessService;
use App\Support\Sales\SalesDeskPageBuilder;
use App\Support\Sales\SalesDeskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesDeskController extends Controller
{
    public function __construct(
        protected SalesDeskPageBuilder $page,
        protected SalesDeskService $desk,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('create', Customer::class);
        $this->authorize('create', SalesOrder::class);

        return view('admin.sales.desk.index', $this->page->build($request));
    }

    public function searchCustomers(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Customer::class);

        return response()->json([
            'results' => $this->desk->searchDesk((string) $request->query('q', '')),
        ]);
    }

    public function materialsHandoff(SalesOrder $salesOrder): View
    {
        $this->authorize('view', $salesOrder);

        $salesOrder->loadMissing('jobCard');
        abort_unless($salesOrder->jobCard, 404);

        $jobCard = $salesOrder->jobCard;
        $materials = app(MaterialReadinessService::class)->assess($jobCard);
        $user = auth()->user();

        return view('admin.sales.desk.materials-modal', [
            'salesOrder' => $salesOrder,
            'jobCard' => $jobCard,
            'materials' => $materials,
            'canOpenJobCard' => $user?->can('view', $jobCard) ?? false,
            'canReceiveStock' => $user?->can('inventory.receive') ?? false,
            'canReserveMaterials' => $user?->can('production.materials.reserve') ?? false,
        ]);
    }
}

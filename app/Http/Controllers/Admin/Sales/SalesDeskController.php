<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Crm\Customer;
use App\Models\Sales\SalesOrder;
use App\Support\Production\MaterialReadinessService;
use App\Support\Production\MaterialRequirementsService;
use App\Support\Sales\SalesDeskPageBuilder;
use App\Support\Sales\SalesDeskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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

        $salesOrder->loadMissing(['jobCard', 'inventoryItem', 'items.inventoryItem']);
        $user = auth()->user();
        $readiness = app(MaterialReadinessService::class);
        $requirements = app(MaterialRequirementsService::class);

        if ($salesOrder->jobCard) {
            $materials = $readiness->assess($salesOrder->jobCard);
            $jobCard = $salesOrder->jobCard;
            $issueType = $materials['has_requirements']
                ? ($materials['ready'] ? 'ready' : 'shortage')
                : 'requirements';
        } else {
            $jobCard = null;
            $sources = $requirements->resolveSourcesFromSalesOrder($salesOrder);
            $materials = $readiness->previewForSalesOrder($salesOrder);
            $issueType = $sources->isEmpty()
                ? 'no_product'
                : ($materials['has_requirements']
                    ? ($materials['ready'] ? 'ready' : 'shortage')
                    : 'bom');
        }

        return view('admin.sales.desk.materials-modal', [
            'salesOrder' => $salesOrder,
            'jobCard' => $jobCard,
            'materials' => $materials,
            'issueType' => $issueType,
            'productName' => $salesOrder->inventoryItem?->item_name
                ?? $salesOrder->items->first()?->item_name,
            'canOpenJobCard' => $jobCard !== null && ($user?->can('view', $jobCard) ?? false),
            'canReceiveStock' => $user?->can('inventory.receive') ?? false,
            'canReserveMaterials' => $user?->can('production.materials.reserve') ?? false,
            'canEditOrder' => $user?->can('update', $salesOrder) ?? false,
            'canManageBom' => $user?->can('production.bom.create') ?? false,
            'resumeUrl' => route('admin.sales.desk', array_filter([
                'customer' => $salesOrder->customer?->getRouteKey(),
                'order' => $salesOrder->getRouteKey(),
                'step' => 4,
            ])),
        ]);
    }

    public function parkWalkIn(Request $request, SalesOrder $salesOrder): RedirectResponse|JsonResponse
    {
        $this->authorize('view', $salesOrder);

        $message = __('Order :order saved. Resume anytime from Needs attention on the Sales Desk.', [
            'order' => $salesOrder->order_number,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'redirect' => route('admin.sales.desk'),
            ]);
        }

        return redirect()
            ->route('admin.sales.desk')
            ->with('status', $message);
    }
}

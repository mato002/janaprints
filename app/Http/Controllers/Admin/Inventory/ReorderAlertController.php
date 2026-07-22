<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Admin\Concerns\HandlesModalFormResponses;
use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventoryReorderAlert;
use App\Models\Inventory\Warehouse;
use App\Support\Inventory\ReorderAlertService;
use App\Support\Inventory\ReturnsToStoreDesk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReorderAlertController extends Controller
{
    use HandlesModalFormResponses, ResolvesInventoryTenant, ReturnsToStoreDesk;

    public function __construct(
        protected ReorderAlertService $alerts,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', InventoryReorderAlert::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        $filters = $request->only(['warehouse_id', 'category_id', 'status', 'critical_only', 'search']);

        return view('admin.inventory.alerts.index', [
            'alerts' => $this->alerts->paginate($companyId, $branchId, $filters),
            'filters' => $filters,
            'warehouses' => Warehouse::query()->forTenant()->orderBy('name')->get(),
            'categories' => InventoryCategory::query()->forTenant()->orderBy('name')->get(),
            'statuses' => \App\Enums\ReorderAlertStatus::cases(),
        ]);
    }

    public function acknowledge(Request $request, InventoryReorderAlert $alert): RedirectResponse
    {
        $this->authorize('acknowledge', $alert);

        try {
            $this->alerts->acknowledge($alert, (int) auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        if ($this->wantsStoreDeskReturn($request)) {
            return redirect()->to($this->storeDeskUrl())->with('status', __('Alert acknowledged.'));
        }

        return back()->with('status', __('Alert acknowledged.'));
    }

    public function resolve(Request $request, InventoryReorderAlert $alert): RedirectResponse
    {
        $this->authorize('resolve', $alert);

        try {
            $this->alerts->resolve($alert, (int) auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        if ($this->wantsStoreDeskReturn($request)) {
            return redirect()->to($this->storeDeskUrl())->with('status', __('Alert resolved.'));
        }

        return back()->with('status', __('Alert resolved.'));
    }

    public function createPurchaseRequest(InventoryReorderAlert $alert): RedirectResponse
    {
        $this->authorize('createPurchaseRequest', $alert);

        try {
            $purchaseRequest = $this->alerts->createPurchaseRequest($alert, auth()->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()
            ->route('admin.procurement.requests.show', $purchaseRequest)
            ->with('status', __('Purchase request created from reorder alert.'));
    }
}

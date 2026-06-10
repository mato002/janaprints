<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Services\Inventory\VirtualWarehouseResolverService;
use App\Support\InventoryStockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VirtualLocationController extends Controller
{
    use ScopesToTenant;

    public function __construct(
        protected VirtualWarehouseResolverService $resolver,
    ) {}

    public function index(): View
    {
        abort_unless(auth()->user()?->can('inventory.virtual-locations.view'), 403);

        $companyId = (int) (tenant()->companyId() ?? auth()->user()->company_id);
        $locations = InventoryStockService::getVirtualWarehouseBalances($companyId);

        return view('admin.inventory.virtual-locations.index', [
            'locations' => $locations,
        ]);
    }

    public function ensureDefaults(): RedirectResponse
    {
        abort_unless(auth()->user()?->can('inventory.virtual-locations.manage'), 403);

        $companyId = (int) (tenant()->companyId() ?? auth()->user()->company_id);
        $this->resolver->ensureDefaults($companyId);

        return redirect()
            ->route('admin.inventory.virtual-locations.index')
            ->with('status', __('Virtual locations have been verified.'));
    }
}

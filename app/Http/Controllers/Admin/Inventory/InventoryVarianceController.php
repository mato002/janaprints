<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\Warehouse;
use App\Support\Inventory\InventoryVarianceExportService;
use App\Support\Inventory\InventoryVarianceReport;
use App\Support\Inventory\InventoryVarianceService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryVarianceController extends Controller
{
    use ResolvesInventoryTenant, ScopesToTenant;

    public function __construct(
        protected InventoryVarianceExportService $exports,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', InventoryVarianceReport::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        $filters = $request->only([
            'warehouse_id', 'status', 'date_from', 'date_to',
            'variance_type', 'category_id', 'item_id',
        ]);

        $variances = InventoryVarianceService::query($companyId, $branchId, $filters)
            ->paginate(config('platform.pagination.default', 15));

        return view('admin.inventory.control.variances.index', [
            'variances' => $variances,
            'filters' => $filters,
            'warehouses' => Warehouse::query()->forTenant()->orderBy('name')->get(),
            'categories' => InventoryCategory::query()->forTenant()->orderBy('name')->get(),
            'items' => InventoryItem::query()->forTenant()->where('is_active', true)->orderBy('item_name')->get(),
            'statuses' => \App\Enums\StockCountStatus::cases(),
        ]);
    }

    public function export(Request $request, string $format = 'csv'): StreamedResponse
    {
        $this->authorize('viewAny', InventoryVarianceReport::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        if (! in_array($format, ['csv', 'excel', 'pdf'], true)) {
            $format = 'csv';
        }

        $filters = $request->only([
            'warehouse_id', 'status', 'date_from', 'date_to',
            'variance_type', 'category_id', 'item_id',
        ]);

        return $this->exports->export($format, $companyId, $branchId, $filters, $request->user());
    }

    public function exportPdf(Request $request): StreamedResponse
    {
        return $this->export($request, 'pdf');
    }
}

<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Enums\StockCountStatus;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\Warehouse;
use App\Support\Inventory\InventoryVarianceReport;
use App\Support\Inventory\InventoryVarianceService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryVarianceController extends Controller
{
    use ResolvesInventoryTenant, ScopesToTenant;

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
            'statuses' => StockCountStatus::cases(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', InventoryVarianceReport::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        $filters = $request->only([
            'warehouse_id', 'status', 'date_from', 'date_to',
            'variance_type', 'category_id', 'item_id',
        ]);

        $rows = InventoryVarianceService::exportRows($companyId, $branchId, $filters);

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Count', 'Warehouse', 'Item', 'System Qty', 'Counted Qty', 'Variance', 'Variance Value', 'Reason', 'Status', 'Count Date']);

            foreach ($rows as $line) {
                fputcsv($handle, [
                    $line->stockCount?->count_number,
                    $line->stockCount?->warehouse?->name,
                    $line->inventoryItem?->item_name,
                    $line->system_quantity,
                    $line->counted_quantity,
                    $line->variance_quantity,
                    $line->variance_value,
                    $line->reason,
                    $line->stockCount?->status?->value,
                    $line->stockCount?->count_date?->format('Y-m-d'),
                ]);
            }

            fclose($handle);
        }, 'inventory-variances.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportPdf(): View
    {
        $this->authorize('viewAny', InventoryVarianceReport::class);

        return view('admin.inventory.control.variances.pdf-placeholder');
    }
}

<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Enums\DocumentType;
use App\Enums\InventoryDocumentStatus;
use App\Enums\StockAdjustmentDirection;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\StockAdjustment;
use App\Models\Inventory\Warehouse;
use App\Support\Platform\NumberingService;
use App\Support\StockAdjustmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockAdjustmentController extends Controller
{
    use ResolvesInventoryTenant, ScopesToTenant;

    public function index(): View
    {
        $this->authorize('viewAny', StockAdjustment::class);

        $adjustments = $this->scopeToTenant(
            StockAdjustment::query()->with(['warehouse', 'adjuster'])->latest('adjustment_date')
        )->paginate(config('platform.pagination.default', 15));

        return view('admin.inventory.adjustments.index', compact('adjustments'));
    }

    public function create(): View
    {
        $this->authorize('create', StockAdjustment::class);

        return view('admin.inventory.adjustments.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', StockAdjustment::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        $header = $request->validate([
            'warehouse_id' => ['required', Rule::exists('warehouses', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'adjustment_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $lines = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id' => ['required', Rule::exists('inventory_items', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'items.*.direction' => ['required', Rule::enum(StockAdjustmentDirection::class)],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ])['items'];

        $adjustment = StockAdjustment::query()->create([
            ...$header,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'adjustment_number' => app(NumberingService::class)->next(
                DocumentType::StockAdjustment,
                $companyId,
                $branchId,
            ),
            'status' => InventoryDocumentStatus::Draft,
            'adjusted_by' => auth()->id(),
        ]);

        foreach ($lines as $line) {
            $adjustment->items()->create($line);
        }

        return redirect()->route('admin.inventory.adjustments.show', $adjustment)->with('status', __('Adjustment created.'));
    }

    public function show(StockAdjustment $adjustment): View
    {
        $this->authorize('view', $adjustment);

        $adjustment->load(['warehouse', 'adjuster', 'items.inventoryItem']);

        return view('admin.inventory.adjustments.show', compact('adjustment'));
    }

    public function post(StockAdjustment $adjustment): RedirectResponse
    {
        $this->authorize('post', $adjustment);

        try {
            StockAdjustmentService::post($adjustment, (int) auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Adjustment posted.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(): array
    {
        return [
            'warehouses' => Warehouse::query()->forTenant()->where('is_active', true)->orderBy('name')->get(),
            'items' => InventoryItem::query()->forTenant()->where('is_active', true)->orderBy('item_name')->get(),
            'directions' => StockAdjustmentDirection::cases(),
        ];
    }
}

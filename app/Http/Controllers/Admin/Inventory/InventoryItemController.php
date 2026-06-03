<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\UnitOfMeasure;
use App\Support\InventoryStockService;
use App\Support\Platform\FormSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryItemController extends Controller
{
    use ResolvesInventoryTenant, ScopesToTenant;

    public function __construct(
        protected FormSettingsService $formSettings,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', InventoryItem::class);

        $items = $this->scopeToTenant(
            InventoryItem::query()->with(['category', 'unitOfMeasure'])
        )->orderBy('item_name')->paginate(15);

        return view('admin.inventory.items.index', compact('items'));
    }

    public function create(): View
    {
        $this->authorize('create', InventoryItem::class);

        return view('admin.inventory.items.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', InventoryItem::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        $item = InventoryItem::query()->create([
            ...$this->validateItem($request, $companyId, $branchId),
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        return redirect()->route('admin.inventory.items.show', $item)->with('status', __('Item created.'));
    }

    public function show(InventoryItem $item): View
    {
        $this->authorize('view', $item);

        $item->load(['category', 'unitOfMeasure']);
        $stockBalance = InventoryStockService::branchBalance($item->id, $item->company_id, $item->branch_id);

        return view('admin.inventory.items.show', compact('item', 'stockBalance'));
    }

    public function edit(InventoryItem $item): View
    {
        $this->authorize('update', $item);

        return view('admin.inventory.items.edit', ['item' => $item, ...$this->formMeta()]);
    }

    public function update(Request $request, InventoryItem $item): RedirectResponse
    {
        $this->authorize('update', $item);

        $item->update($this->validateItem($request, $item->company_id, $item->branch_id));
        InventoryStockService::syncReorderAlerts($item->fresh());

        return redirect()->route('admin.inventory.items.show', $item)->with('status', __('Item updated.'));
    }

    public function destroy(InventoryItem $item): RedirectResponse
    {
        $this->authorize('delete', $item);

        $item->delete();

        return redirect()->route('admin.inventory.items.index')->with('status', __('Item deleted.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateItem(Request $request, int $companyId, int $branchId): array
    {
        return $request->validate($this->formSettings->mergeValidationRules('inventory_item', [
            'inventory_category_id' => [Rule::exists('inventory_categories', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'unit_of_measure_id' => [Rule::exists('units_of_measure', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'sku' => ['string', 'max:50'],
            'item_name' => ['string', 'max:255'],
            'description' => ['string'],
            'reorder_level' => ['numeric', 'min:0'],
            'reorder_quantity' => ['numeric', 'min:0'],
            'standard_cost' => ['numeric', 'min:0'],
            'is_active' => ['boolean'],
        ], $companyId, $branchId));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(): array
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        return [
            'formFields' => $this->formSettings->resolvedFields('inventory_item', $companyId, $branchId),
            'categories' => InventoryCategory::query()->forTenant()->where('is_active', true)->orderBy('name')->get(),
            'units' => UnitOfMeasure::query()->forTenant()->where('is_active', true)->orderBy('name')->get(),
        ];
    }
}

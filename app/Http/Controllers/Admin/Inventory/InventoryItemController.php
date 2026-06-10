<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Enums\InventoryStockRole;
use App\Http\Controllers\Admin\Concerns\HandlesFormCustomFields;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Brand;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventorySubcategory;
use App\Models\Inventory\ItemAttribute;
use App\Models\Inventory\UnitOfMeasure;
use App\Support\Catalogue\CatalogueService;
use App\Support\Catalogue\ItemAttributeService;
use App\Support\InventoryStockService;
use App\Support\Platform\FormSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryItemController extends Controller
{
    use HandlesFormCustomFields, ResolvesInventoryTenant, ScopesToTenant;

    public function __construct(
        protected FormSettingsService $formSettings,
        protected CatalogueService $catalogue,
        protected ItemAttributeService $itemAttributes,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', InventoryItem::class);

        $stockRole = $request->string('stock_role')->toString() ?: null;

        $query = $this->scopeToTenant(
            InventoryItem::query()->with(['category', 'subcategory', 'brand', 'unitOfMeasure', 'images'])
        )->orderBy('item_name');

        if ($stockRole !== null && $stockRole !== '' && $stockRole !== 'all') {
            $query->where('stock_role', $stockRole);
        }

        $items = $query->paginate(15)->withQueryString();

        return view('admin.inventory.items.index', [
            'items' => $items,
            'stockRole' => $stockRole ?? 'all',
            'stockRoles' => InventoryStockRole::cases(),
        ]);
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

        $data = $this->validateItem($request, $companyId, $branchId);
        [$data, $customData] = $this->partitionCustomFields('inventory_item', $data, $companyId, $branchId);
        $data['sku'] = $this->resolveSku($data, $request);

        $item = InventoryItem::query()->create([
            ...$data,
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $this->itemAttributes->sync($item, $request->input('attributes', []));
        $this->syncCustomFields($item, 'inventory_item', $customData, $companyId);

        return redirect()->route('admin.inventory.items.show', $item)->with('status', __('Item created.'));
    }

    public function show(InventoryItem $item): View
    {
        $this->authorize('view', $item);

        $item->load([
            'category', 'subcategory', 'brand', 'unitOfMeasure',
            'attributeValues.attribute', 'attributeValues.option', 'images',
            'priceListItems.priceList',
        ]);
        $stockBalance = InventoryStockService::branchBalance($item->id, $item->company_id, $item->branch_id);

        return view('admin.inventory.items.show', compact('item', 'stockBalance'));
    }

    public function edit(InventoryItem $item): View
    {
        $this->authorize('update', $item);

        $item->load('attributeValues');

        return view('admin.inventory.items.edit', ['item' => $item, ...$this->formMeta($item)]);
    }

    public function update(Request $request, InventoryItem $item): RedirectResponse
    {
        $this->authorize('update', $item);

        $data = $this->validateItem($request, $item->company_id, $item->branch_id, $item);
        [$data, $customData] = $this->partitionCustomFields('inventory_item', $data, $item->company_id, $item->branch_id);
        $data['sku'] = $this->resolveSku($data, $request);

        $item->update($data);
        $this->itemAttributes->sync($item, $request->input('attributes', []));
        $this->syncCustomFields($item, 'inventory_item', $customData, $item->company_id);
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
        return $this->formSettings->validateRequest($request, 'inventory_item', [
            'inventory_category_id' => [Rule::exists('inventory_categories', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'subcategory_id' => [Rule::exists('inventory_subcategories', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'brand_id' => [Rule::exists('brands', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'unit_of_measure_id' => [Rule::exists('units_of_measure', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'sku' => ['string', 'max:50'],
            'item_code' => ['string', 'max:50'],
            'item_name' => ['string', 'max:255'],
            'description' => ['string'],
            'reorder_level' => ['numeric', 'min:0'],
            'reorder_quantity' => ['numeric', 'min:0'],
            'standard_cost' => ['numeric', 'min:0'],
            'is_active' => ['boolean'],
            'stock_role' => ['required', Rule::enum(InventoryStockRole::class)],
        ], $companyId, $branchId);
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(?InventoryItem $item = null): array
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        return [
            'formFields' => $this->formSettings->resolvedFields('inventory_item', $companyId, $branchId, $item),
            'categories' => InventoryCategory::query()->forTenant()->where('is_active', true)->orderBy('name')->get(),
            'subcategories' => InventorySubcategory::query()->forTenant()->with('category')->where('is_active', true)->orderBy('name')->get(),
            'brands' => Brand::query()->forTenant()->where('is_active', true)->orderBy('name')->get(),
            'units' => UnitOfMeasure::query()->forTenant()->where('is_active', true)->orderBy('name')->get(),
            'attributes' => ItemAttribute::query()->forTenant()->with('options')->where('is_active', true)->orderBy('name')->get(),
            'stockRoles' => InventoryStockRole::cases(),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function resolveSku(array $data, Request $request): string
    {
        if (filled($data['sku'] ?? null)) {
            return (string) $data['sku'];
        }

        $category = InventoryCategory::query()->findOrFail($data['inventory_category_id']);
        $subcategory = filled($data['subcategory_id'] ?? null)
            ? InventorySubcategory::query()->find($data['subcategory_id'])
            : null;
        $brand = filled($data['brand_id'] ?? null)
            ? Brand::query()->find($data['brand_id'])
            : null;

        return $this->catalogue->structuredSku($category, $subcategory, $brand, (string) $data['item_name'], $request->input('sku_parts', []));
    }
}

<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Enums\InventoryStockRole;
use App\Http\Controllers\Admin\Concerns\HandlesFormCustomFields;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventoryItem;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\WorkCenter;
use App\Models\Inventory\InventorySubcategory;
use App\Models\Inventory\ItemAttribute;
use App\Models\Inventory\UnitOfMeasure;
use App\Support\Catalogue\CatalogueService;
use App\Support\Catalogue\ItemAttributeService;
use App\Support\InventoryStockService;
use App\Support\Production\ProductBomService;
use App\Support\Production\ProductQcChecklistService;
use App\Support\Production\ProductionRouteService;
use App\Support\Platform\FormSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InventoryItemController extends Controller
{
    use HandlesFormCustomFields, ResolvesInventoryTenant, ScopesToTenant;

    public function __construct(
        protected FormSettingsService $formSettings,
        protected CatalogueService $catalogue,
        protected ItemAttributeService $itemAttributes,
        protected ProductionRouteService $productionRoutes,
        protected ProductBomService $productBoms,
        protected ProductQcChecklistService $productQcChecklists,
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

        $this->normalizeMaterialRequirements($request);

        $data = $this->validateItem($request, $companyId, $branchId);
        $this->ensureSubcategoryMatchesCategory($data);
        [$data, $customData] = $this->partitionCustomFields('inventory_item', $data, $companyId, $branchId);
        $data['sku'] = $this->resolveSku($data, $request);
        $data['uses_serial_numbers'] = $request->boolean('uses_serial_numbers');
        $data['requires_customer_approval'] = $request->boolean('requires_customer_approval');

        $item = InventoryItem::query()->create([
            ...$data,
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $this->itemAttributes->sync($item, $request->input('attributes', []));
        $this->syncCustomFields($item, 'inventory_item', $customData, $companyId);
        $this->syncProductionRoute($item, $request->input('route_steps', []));
        $this->syncProductMaterials($item, $request->input('material_requirements', []), (int) auth()->id());
        $this->syncProductQcChecklist($item, $request->input('qc_checklist', []), (int) auth()->id());

        return redirect()->route('admin.inventory.items.show', $item)->with('status', __('Item created.'));
    }

    public function show(InventoryItem $item): View
    {
        $this->authorize('view', $item);

        $item->load([
            'category', 'subcategory', 'brand', 'unitOfMeasure',
            'attributeValues.attribute', 'attributeValues.option', 'images',
            'priceListItems.priceList', 'productionRouteSteps',
        ]);
        $stockBalance = InventoryStockService::branchBalance($item->id, $item->company_id, $item->branch_id);

        return view('admin.inventory.items.show', compact('item', 'stockBalance'));
    }

    public function edit(InventoryItem $item): View
    {
        $this->authorize('update', $item);

        $item->load('attributeValues', 'productionRouteSteps');

        return view('admin.inventory.items.edit', ['item' => $item, ...$this->formMeta($item)]);
    }

    public function update(Request $request, InventoryItem $item): RedirectResponse
    {
        $this->authorize('update', $item);

        $this->normalizeMaterialRequirements($request);

        $data = $this->validateItem($request, $item->company_id, $item->branch_id);
        $this->ensureSubcategoryMatchesCategory($data);
        [$data, $customData] = $this->partitionCustomFields('inventory_item', $data, $item->company_id, $item->branch_id);
        $data['sku'] = $this->resolveSku($data, $request);
        $data['uses_serial_numbers'] = $request->boolean('uses_serial_numbers');
        $data['requires_customer_approval'] = $request->boolean('requires_customer_approval');

        $item->update($data);
        $this->itemAttributes->sync($item, $request->input('attributes', []));
        $this->syncCustomFields($item, 'inventory_item', $customData, $item->company_id);
        $this->syncProductionRoute($item, $request->input('route_steps', []));

        // Only sync BOM when at least one material was submitted — empty Alpine rows
        // must not wipe an existing bill of materials while editing stock role / name.
        if ($this->materialRequirementsHaveItems($request)) {
            $this->syncProductMaterials($item, $request->input('material_requirements', []), (int) auth()->id());
        }

        $this->syncProductQcChecklist($item, $request->input('qc_checklist', []), (int) auth()->id());
        InventoryStockService::syncReorderAlerts($item->fresh());

        return redirect()->route('admin.inventory.items.show', $item)->with('status', __('Item updated.'));
    }

    public function classifyAsFinishedGood(Request $request, InventoryItem $item): RedirectResponse
    {
        $this->authorize('classify', $item);

        $item->update(['stock_role' => InventoryStockRole::FinishedGood]);
        InventoryStockService::syncReorderAlerts($item->fresh());

        $status = __(':item is now classified as a finished good.', ['item' => $item->item_name]);

        $jobCardId = $request->integer('production_job_card_id');
        if ($jobCardId > 0) {
            $jobCard = ProductionJobCard::query()->find($jobCardId);
            if ($jobCard && $jobCard->company_id === $item->company_id && $request->user()?->can('view', $jobCard)) {
                return redirect()
                    ->route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'outputs'])
                    ->with('status', $status);
            }
        }

        return redirect()->route('admin.inventory.items.show', $item)->with('status', $status);
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
            'subcategory_id' => [
                Rule::exists('inventory_subcategories', 'id')
                    ->where('company_id', $companyId)
                    ->where('branch_id', $branchId)
                    ->where(fn ($query) => $query->where('inventory_category_id', $request->integer('inventory_category_id'))),
            ],
            'brand_name' => ['nullable', 'string', 'max:255'],
            'unit_of_measure_id' => [Rule::exists('units_of_measure', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'sku' => ['string', 'max:50'],
            'item_name' => ['string', 'max:255'],
            'description' => ['string'],
            'reorder_level' => ['numeric', 'min:0'],
            'reorder_quantity' => ['numeric', 'min:0'],
            'standard_cost' => ['numeric', 'min:0'],
            'is_active' => ['boolean'],
            'stock_role' => ['required', Rule::enum(InventoryStockRole::class)],
            'uses_serial_numbers' => ['boolean'],
            'requires_customer_approval' => ['boolean'],
            'serial_prefix' => ['nullable', 'string', 'max:30'],
            'serial_padding_length' => ['nullable', 'integer', 'min:1', 'max:12'],
            'route_steps' => ['nullable', 'array'],
            'route_steps.*.step_name' => ['nullable', 'string', 'max:255'],
            'route_steps.*.sequence' => ['nullable', 'integer', 'min:1'],
            'route_steps.*.work_center_id' => ['nullable', 'exists:work_centers,id'],
            'material_requirements' => ['nullable', 'array'],
            'material_requirements.*.inventory_item_id' => ['nullable', 'exists:inventory_items,id'],
            'material_requirements.*.quantity_per_unit' => ['nullable', 'numeric', 'min:0.0001'],
            'material_requirements.*.quantity_formula' => ['nullable', 'string', 'max:120'],
            'material_requirements.*.is_active' => ['nullable', 'boolean'],
            'qc_checklist' => ['nullable', 'array'],
            'qc_checklist.*.label' => ['nullable', 'string', 'max:120'],
            'qc_checklist.*.is_active' => ['nullable', 'boolean'],
        ], $companyId, $branchId);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function ensureSubcategoryMatchesCategory(array $data): void
    {
        if (! filled($data['subcategory_id'] ?? null)) {
            return;
        }

        $belongs = InventorySubcategory::query()
            ->whereKey($data['subcategory_id'])
            ->where('inventory_category_id', $data['inventory_category_id'])
            ->exists();

        if (! $belongs) {
            throw ValidationException::withMessages([
                'subcategory_id' => __('The selected subcategory does not belong to the chosen category.'),
            ]);
        }
    }

    protected function normalizeMaterialRequirements(Request $request): void
    {
        $lines = collect($request->input('material_requirements', []))
            ->map(function (array $line): array {
                $id = $line['inventory_item_id'] ?? null;
                $line['inventory_item_id'] = ($id === '' || $id === null) ? null : $id;

                return $line;
            })
            ->values()
            ->all();

        $request->merge(['material_requirements' => $lines]);
    }

    protected function materialRequirementsHaveItems(Request $request): bool
    {
        return collect($request->input('material_requirements', []))
            ->contains(fn (array $line) => filled($line['inventory_item_id'] ?? null));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(?InventoryItem $item = null): array
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();
        $categoryId = old('inventory_category_id', $item?->inventory_category_id);

        return [
            'formFields' => $this->formSettings->resolvedFields('inventory_item', $companyId, $branchId, $item),
            'categories' => InventoryCategory::query()->forTenant()->where('is_active', true)->orderBy('name')->get(),
            'subcategories' => $categoryId
                ? InventorySubcategory::query()
                    ->forTenant()
                    ->where('inventory_category_id', $categoryId)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get()
                : collect(),
            'units' => UnitOfMeasure::query()->forTenant()->where('is_active', true)->orderBy('name')->get(),
            'attributes' => ItemAttribute::query()
                ->forTenant()
                ->with('options')
                ->where('is_active', true)
                ->where('code', '!=', 'FINISH')
                ->when($categoryId, function ($query) use ($categoryId) {
                    $query->where(function ($builder) use ($categoryId) {
                        $builder->whereNull('inventory_category_id')
                            ->orWhere('inventory_category_id', $categoryId);
                    });
                }, fn ($query) => $query->whereRaw('1 = 0'))
                ->orderBy('name')
                ->get(),
            'stockRoles' => InventoryStockRole::cases(),
            'workCenters' => WorkCenter::query()->forTenant()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
            'rawMaterials' => InventoryItem::query()->forTenant()->where('is_active', true)->orderBy('item_name')->get(['id', 'sku', 'item_name']),
            'productBomLines' => $item
                ? (app(ProductBomService::class)->findActiveForFinishedItem($item->company_id, $item->branch_id, $item->id)?->lines ?? collect())
                : collect(),
            'productQcChecklistLines' => $item
                ? (app(ProductQcChecklistService::class)->findActiveForFinishedItem($item->company_id, $item->branch_id, $item->id)?->lines ?? collect())
                : collect(),
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
        $brandName = filled($data['brand_name'] ?? null) ? (string) $data['brand_name'] : null;

        return $this->catalogue->structuredSku($category, $subcategory, $brandName, (string) $data['item_name'], $request->input('sku_parts', []));
    }

    /**
     * @param  list<array<string, mixed>>  $steps
     */
    protected function syncProductionRoute(InventoryItem $item, array $steps): void
    {
        $this->productionRoutes->syncProductRoute($item, $steps);
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    protected function syncProductMaterials(InventoryItem $item, array $lines, int $userId): void
    {
        $this->productBoms->syncFromCatalogItem($item, $lines, $userId);
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    protected function syncProductQcChecklist(InventoryItem $item, array $lines, int $userId): void
    {
        $this->productQcChecklists->syncFromCatalogItem($item, $lines, $userId);
    }
}

<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\UnitOfMeasure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryCategoryController extends Controller
{
    use ResolvesInventoryTenant;

    public function index(): View
    {
        abort_unless(auth()->user()?->can('catalogue.view'), 403);

        $categories = InventoryCategory::query()
            ->forTenant()
            ->with(['defaultUom'])
            ->withCount(['items', 'subcategories'])
            ->orderBy('name')
            ->paginate(15);

        return view('admin.inventory.catalogue.categories.index', compact('categories'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->can('catalogue.create'), 403);

        return view('admin.inventory.catalogue.categories.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('catalogue.create'), 403);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        $category = InventoryCategory::query()->create([
            ...$this->validateCategory($request, $companyId, $branchId),
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        return redirect()->route('admin.inventory.catalogue.categories.index')->with('status', __('Category created: :name', ['name' => $category->name]));
    }

    public function edit(InventoryCategory $category): View
    {
        abort_unless(auth()->user()?->can('catalogue.edit'), 403);
        $this->ensureTenant($category);

        return view('admin.inventory.catalogue.categories.edit', ['category' => $category, ...$this->formMeta()]);
    }

    public function update(Request $request, InventoryCategory $category): RedirectResponse
    {
        abort_unless(auth()->user()?->can('catalogue.edit'), 403);
        $this->ensureTenant($category);

        $category->update($this->validateCategory($request, $category->company_id, $category->branch_id, $category));

        return redirect()->route('admin.inventory.catalogue.categories.index')->with('status', __('Category updated.'));
    }

    public function destroy(InventoryCategory $category): RedirectResponse
    {
        abort_unless(auth()->user()?->can('catalogue.delete'), 403);
        $this->ensureTenant($category);

        if ($category->items()->exists() || $category->subcategories()->exists()) {
            return back()->withErrors(['category' => __('Category is in use and cannot be deleted. Deactivate it instead.')]);
        }

        $category->delete();

        return back()->with('status', __('Category removed.'));
    }

    protected function validateCategory(Request $request, int $companyId, int $branchId, ?InventoryCategory $category = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('inventory_categories', 'code')->where('company_id', $companyId)->where('branch_id', $branchId)->ignore($category)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'default_uom_id' => ['nullable', Rule::exists('units_of_measure', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'reorder_behavior' => ['required', Rule::in(['standard', 'made_to_order', 'non_stock', 'critical'])],
            'is_active' => ['boolean'],
        ]);
    }

    protected function formMeta(): array
    {
        return [
            'units' => UnitOfMeasure::query()->forTenant()->where('is_active', true)->orderBy('name')->get(),
        ];
    }

    protected function ensureTenant(InventoryCategory $category): void
    {
        abort_unless($category->company_id === tenant()->companyId() && $category->branch_id === tenant()->branchId(), 404);
    }
}

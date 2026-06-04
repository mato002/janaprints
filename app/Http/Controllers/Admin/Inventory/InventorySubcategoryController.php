<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventorySubcategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventorySubcategoryController extends Controller
{
    use ResolvesInventoryTenant;

    public function index(): View
    {
        abort_unless(auth()->user()?->can('catalogue.view'), 403);

        $subcategories = InventorySubcategory::query()
            ->forTenant()
            ->with('category')
            ->withCount('items')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.inventory.catalogue.subcategories.index', compact('subcategories'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->can('catalogue.create'), 403);

        return view('admin.inventory.catalogue.subcategories.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('catalogue.create'), 403);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        InventorySubcategory::query()->create([
            ...$this->validateSubcategory($request, $companyId, $branchId),
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        return redirect()->route('admin.inventory.catalogue.subcategories.index')->with('status', __('Subcategory created.'));
    }

    public function edit(InventorySubcategory $subcategory): View
    {
        abort_unless(auth()->user()?->can('catalogue.edit'), 403);
        $this->ensureTenant($subcategory);

        return view('admin.inventory.catalogue.subcategories.edit', ['subcategory' => $subcategory, ...$this->formMeta()]);
    }

    public function update(Request $request, InventorySubcategory $subcategory): RedirectResponse
    {
        abort_unless(auth()->user()?->can('catalogue.edit'), 403);
        $this->ensureTenant($subcategory);

        $subcategory->update($this->validateSubcategory($request, $subcategory->company_id, $subcategory->branch_id, $subcategory));

        return redirect()->route('admin.inventory.catalogue.subcategories.index')->with('status', __('Subcategory updated.'));
    }

    public function destroy(InventorySubcategory $subcategory): RedirectResponse
    {
        abort_unless(auth()->user()?->can('catalogue.delete'), 403);
        $this->ensureTenant($subcategory);

        if ($subcategory->items()->exists()) {
            return back()->withErrors(['subcategory' => __('Subcategory is in use and cannot be deleted. Deactivate it instead.')]);
        }

        $subcategory->delete();

        return back()->with('status', __('Subcategory removed.'));
    }

    protected function validateSubcategory(Request $request, int $companyId, int $branchId, ?InventorySubcategory $subcategory = null): array
    {
        return $request->validate([
            'inventory_category_id' => ['required', Rule::exists('inventory_categories', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'code' => ['required', 'string', 'max:50', Rule::unique('inventory_subcategories', 'code')->where('company_id', $companyId)->where('branch_id', $branchId)->where('inventory_category_id', $request->integer('inventory_category_id'))->ignore($subcategory)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);
    }

    protected function formMeta(): array
    {
        return [
            'categories' => InventoryCategory::query()->forTenant()->where('is_active', true)->orderBy('name')->get(),
        ];
    }

    protected function ensureTenant(InventorySubcategory $subcategory): void
    {
        abort_unless($subcategory->company_id === tenant()->companyId() && $subcategory->branch_id === tenant()->branchId(), 404);
    }
}

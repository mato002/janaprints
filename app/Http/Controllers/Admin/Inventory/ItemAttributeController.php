<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\ItemAttribute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ItemAttributeController extends Controller
{
    use ResolvesInventoryTenant;

    public function index(): View
    {
        abort_unless(auth()->user()?->can('catalogue.view'), 403);

        $attributes = ItemAttribute::query()->forTenant()->with(['category', 'options'])->orderBy('name')->paginate(15);

        return view('admin.inventory.catalogue.attributes.index', compact('attributes'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->can('catalogue.create'), 403);

        return view('admin.inventory.catalogue.attributes.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('catalogue.create'), 403);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();
        $attribute = ItemAttribute::query()->create([
            ...$this->validateAttribute($request, $companyId, $branchId),
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $this->syncOptions($attribute, $request->input('options', ''));

        return redirect()->route('admin.inventory.catalogue.attributes.index')->with('status', __('Attribute created.'));
    }

    public function edit(ItemAttribute $attribute): View
    {
        abort_unless(auth()->user()?->can('catalogue.edit'), 403);
        $this->ensureTenant($attribute);

        $attribute->load('options');

        return view('admin.inventory.catalogue.attributes.edit', ['attribute' => $attribute, ...$this->formMeta()]);
    }

    public function update(Request $request, ItemAttribute $attribute): RedirectResponse
    {
        abort_unless(auth()->user()?->can('catalogue.edit'), 403);
        $this->ensureTenant($attribute);

        $attribute->update($this->validateAttribute($request, $attribute->company_id, $attribute->branch_id, $attribute));
        $this->syncOptions($attribute, $request->input('options', ''));

        return redirect()->route('admin.inventory.catalogue.attributes.index')->with('status', __('Attribute updated.'));
    }

    public function destroy(ItemAttribute $attribute): RedirectResponse
    {
        abort_unless(auth()->user()?->can('catalogue.delete'), 403);
        $this->ensureTenant($attribute);

        if ($attribute->itemValues()->exists()) {
            return back()->withErrors(['attribute' => __('Attribute is in use and cannot be deleted. Deactivate it instead.')]);
        }

        $attribute->delete();

        return back()->with('status', __('Attribute removed.'));
    }

    protected function validateAttribute(Request $request, int $companyId, int $branchId, ?ItemAttribute $attribute = null): array
    {
        return $request->validate([
            'inventory_category_id' => ['nullable', Rule::exists('inventory_categories', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'code' => ['required', 'string', 'max:50', Rule::unique('item_attributes', 'code')->where('company_id', $companyId)->where('branch_id', $branchId)->ignore($attribute)],
            'name' => ['required', 'string', 'max:255'],
            'data_type' => ['required', Rule::in(['text', 'number', 'select'])],
            'is_required' => ['boolean'],
            'is_active' => ['boolean'],
        ]);
    }

    protected function syncOptions(ItemAttribute $attribute, ?string $options): void
    {
        if ($attribute->data_type !== 'select') {
            $attribute->options()->delete();

            return;
        }

        $keep = [];
        foreach (preg_split('/\r\n|\r|\n/', (string) $options) as $index => $line) {
            $label = trim($line);
            if ($label === '') {
                continue;
            }

            $option = $attribute->options()->updateOrCreate(
                ['value' => str($label)->slug('-')->upper()->toString()],
                ['label' => $label, 'sort_order' => $index, 'is_active' => true],
            );
            $keep[] = $option->id;
        }

        if ($keep !== []) {
            $attribute->options()->whereNotIn('id', $keep)->delete();
        }
    }

    protected function formMeta(): array
    {
        return [
            'categories' => InventoryCategory::query()->forTenant()->where('is_active', true)->orderBy('name')->get(),
        ];
    }

    protected function ensureTenant(ItemAttribute $attribute): void
    {
        abort_unless($attribute->company_id === tenant()->companyId() && $attribute->branch_id === tenant()->branchId(), 404);
    }
}

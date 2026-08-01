<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\UnitOfMeasure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UnitOfMeasureController extends Controller
{
    use ResolvesInventoryTenant;

    public function index(Request $request): View
    {
        abort_unless(auth()->user()?->can('catalogue.view'), 403);

        $status = $request->string('status')->toString() ?: 'all';

        $units = UnitOfMeasure::query()
            ->forTenant()
            ->with('baseUnit')
            ->withCount(['items', 'categories', 'derivedUnits'])
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(config('platform.pagination.default', 15));

        return view('admin.inventory.catalogue.units.index', [
            'units' => $units,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->can('catalogue.create'), 403);

        return view('admin.inventory.catalogue.units.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('catalogue.create'), 403);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        $unit = UnitOfMeasure::query()->create([
            ...$this->validateUnit($request, $companyId, $branchId),
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        return redirect()
            ->route('admin.inventory.catalogue.units.show', $unit)
            ->with('status', __('Unit of measure created: :name', ['name' => $unit->name]));
    }

    public function show(UnitOfMeasure $unit): View
    {
        abort_unless(auth()->user()?->can('catalogue.view'), 403);
        $this->ensureTenant($unit);

        $unit->load(['baseUnit', 'derivedUnits']);
        $items = $unit->items()->with('category')->orderBy('item_name')->limit(25)->get();

        return view('admin.inventory.catalogue.units.show', compact('unit', 'items'));
    }

    public function edit(UnitOfMeasure $unit): View
    {
        abort_unless(auth()->user()?->can('catalogue.edit'), 403);
        $this->ensureTenant($unit);

        return view('admin.inventory.catalogue.units.edit', ['unit' => $unit, ...$this->formMeta($unit)]);
    }

    public function update(Request $request, UnitOfMeasure $unit): RedirectResponse
    {
        abort_unless(auth()->user()?->can('catalogue.edit'), 403);
        $this->ensureTenant($unit);

        $unit->update($this->validateUnit($request, $unit->company_id, $unit->branch_id, $unit));

        return redirect()
            ->route('admin.inventory.catalogue.units.show', $unit)
            ->with('status', __('Unit of measure updated.'));
    }

    public function deactivate(UnitOfMeasure $unit): RedirectResponse
    {
        abort_unless(auth()->user()?->can('catalogue.edit'), 403);
        $this->ensureTenant($unit);

        $unit->update(['is_active' => false]);

        return back()->with('status', __('Unit of measure deactivated.'));
    }

    public function destroy(UnitOfMeasure $unit): RedirectResponse
    {
        abort_unless(auth()->user()?->can('catalogue.delete'), 403);
        $this->ensureTenant($unit);

        if ($unit->isInUse()) {
            return back()->withErrors([
                'unit' => __('Unit is in use and cannot be deleted. Deactivate it instead.'),
            ]);
        }

        $unit->delete();

        return redirect()
            ->route('admin.inventory.catalogue.units.index')
            ->with('status', __('Unit of measure removed.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateUnit(Request $request, int $companyId, int $branchId, ?UnitOfMeasure $unit = null): array
    {
        $data = $request->validate([
            'code' => array_merge(
                $this->nullableCodeRules(50),
                [Rule::unique('units_of_measure', 'code')
                    ->where('company_id', $companyId)
                    ->where('branch_id', $branchId)
                    ->ignore($unit)],
            ),
            'name' => ['required', 'string', 'max:255'],
            'base_unit_id' => [
                'nullable',
                Rule::exists('units_of_measure', 'id')
                    ->where('company_id', $companyId)
                    ->where('branch_id', $branchId)
                    ->when($unit, fn ($rule) => $rule->whereNot('id', $unit->id)),
            ],
            'conversion_factor' => ['nullable', 'numeric', 'min:0.0001'],
            'is_active' => ['boolean'],
        ]);

        if (empty($data['base_unit_id'])) {
            $data['base_unit_id'] = null;
            $data['conversion_factor'] = 1;
        } else {
            $data['conversion_factor'] = (float) ($data['conversion_factor'] ?? 1);
        }

        $data['code'] = $this->resolveBranchScopedCode(
            $request,
            'name',
            UnitOfMeasure::class,
            $companyId,
            $branchId,
            $unit?->id,
        );

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(?UnitOfMeasure $unit = null): array
    {
        return [
            'baseUnits' => UnitOfMeasure::query()
                ->forTenant()
                ->whereNull('base_unit_id')
                ->when($unit, fn ($q) => $q->where('id', '!=', $unit->id))
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ];
    }

    protected function ensureTenant(UnitOfMeasure $unit): void
    {
        abort_unless(
            $unit->company_id === tenant()->companyId() && $unit->branch_id === tenant()->branchId(),
            404,
        );
    }
}

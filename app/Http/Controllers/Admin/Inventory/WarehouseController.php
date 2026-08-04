<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Admin\Concerns\HandlesFormCustomFields;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\StockAdjustment;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\StockReceipt;
use App\Models\Inventory\Warehouse;
use App\Support\Platform\FormSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    use HandlesFormCustomFields, ResolvesInventoryTenant, ScopesToTenant;

    public function __construct(
        protected FormSettingsService $formSettings,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Warehouse::class);

        $warehouses = $this->scopeToTenant(
            Warehouse::query()->physical()->with(['branch', 'managers'])->withCount('managers')
        )->orderBy('name')->paginate(config('platform.pagination.default', 15));

        $branches = Branch::query()
            ->where('company_id', tenant()->companyId() ?? auth()->user()->company_id)
            ->orderBy('name')
            ->get();

        return view('admin.inventory.warehouses.index', compact('warehouses', 'branches'));
    }

    public function create(): View
    {
        $this->authorize('create', Warehouse::class);

        return view('admin.inventory.warehouses.create', [
            'warehouse' => null,
            ...$this->warehouseFormMeta('warehouse.create'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Warehouse::class);

        ['companyId' => $companyId, 'branchId' => $tenantBranchId] = $this->tenantIds();

        $data = $this->validateWarehouse($request, $companyId, $tenantBranchId);
        $branchId = $data['branch_id'] ?? $tenantBranchId;
        [$data, $customData] = $this->partitionCustomFields('warehouse.create', $data, $companyId, $branchId);

        $warehouse = Warehouse::query()->create([
            ...$data,
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $this->syncCustomFields($warehouse, 'warehouse.create', $customData, $companyId);

        return redirect()->route('admin.inventory.warehouses.show', $warehouse)->with('status', __('Warehouse created.'));
    }

    public function show(Warehouse $warehouse): View
    {
        $this->authorize('view', $warehouse);

        $warehouse->load('managers');
        $balances = $this->warehouseBalances($warehouse);
        $categories = InventoryCategory::query()
            ->where('company_id', $warehouse->company_id)
            ->where('branch_id', $warehouse->branch_id)
            ->orderBy('name')
            ->get();

        return view('admin.inventory.warehouses.show', compact('warehouse', 'balances', 'categories'));
    }

    public function edit(Warehouse $warehouse): View
    {
        $this->authorize('update', $warehouse);

        return view('admin.inventory.warehouses.edit', [
            'warehouse' => $warehouse,
            ...$this->warehouseFormMeta('warehouse.edit', $warehouse->company_id, $warehouse->branch_id, $warehouse),
        ]);
    }

    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $this->authorize('update', $warehouse);

        $data = $this->validateWarehouse($request, $warehouse->company_id, $warehouse->branch_id, $warehouse);
        [$data, $customData] = $this->partitionCustomFields('warehouse.edit', $data, $warehouse->company_id, $warehouse->branch_id);

        if ($warehouse->is_virtual) {
            return back()->withErrors([
                'warehouse' => __('Virtual warehouses are managed from Virtual Locations.'),
            ]);
        }

        $warehouse->update($data);
        $this->syncCustomFields($warehouse, 'warehouse.edit', $customData, $warehouse->company_id);

        return redirect()->route('admin.inventory.warehouses.show', $warehouse)->with('status', __('Warehouse updated.'));
    }

    public function deactivate(Warehouse $warehouse): RedirectResponse
    {
        $this->authorize('update', $warehouse);

        $warehouse->update(['is_active' => false]);

        return back()->with('status', __('Warehouse deactivated. Operational history has been preserved.'));
    }

    public function reactivate(Warehouse $warehouse): RedirectResponse
    {
        $this->authorize('update', $warehouse);

        $warehouse->update(['is_active' => true]);

        return back()->with('status', __('Warehouse reactivated.'));
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $this->authorize('delete', $warehouse);

        if ($warehouse->is_virtual) {
            try {
                \App\Support\Inventory\VirtualWarehouseGuard::assertDeletable($warehouse);
            } catch (ValidationException $e) {
                return back()->withErrors($e->errors());
            }
        }

        if ($this->hasOperationalHistory($warehouse)) {
            return back()->withErrors([
                'warehouse' => __('This warehouse has operational history and cannot be deleted. You may deactivate it instead.'),
            ]);
        }

        $warehouse->delete();

        return redirect()->route('admin.inventory.warehouses.index')->with('status', __('Warehouse removed.'));
    }

    public function balances(Warehouse $warehouse): View
    {
        $this->authorize('view', $warehouse);

        $balances = $this->warehouseBalances($warehouse);
        $categories = InventoryCategory::query()
            ->where('company_id', $warehouse->company_id)
            ->where('branch_id', $warehouse->branch_id)
            ->orderBy('name')
            ->get();

        return view('admin.inventory.warehouses.balances', compact('warehouse', 'balances', 'categories'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateWarehouse(Request $request, int $companyId, int $branchId, ?Warehouse $warehouse = null): array
    {
        $formKey = $warehouse ? 'warehouse.edit' : 'warehouse.create';

        $effectiveBranchId = $this->formSettings->isVisible($formKey, 'branch_id', $companyId, $branchId)
            ? (int) $request->input('branch_id')
            : ($warehouse?->branch_id ?? $branchId);

        $this->formSettings->withoutHiddenInputs($request, $formKey, $companyId, $branchId);

        $rules = $this->formSettings->mergeValidationRules($formKey, [
            'code' => array_merge(
                $this->nullableCodeRules(50),
                [
                    Rule::unique('warehouses', 'code')
                        ->where('company_id', $companyId)
                        ->where('branch_id', $effectiveBranchId)
                        ->ignore($warehouse),
                ],
            ),
            'name' => ['string', 'max:255'],
            'branch_id' => [Rule::exists('branches', 'id')->where('company_id', $companyId)->where('is_active', true)],
            'location' => ['string', 'max:255'],
            'notes' => ['string'],
            'description' => ['string'],
            'is_active' => ['boolean'],
        ], $companyId, $branchId, serverProvidedFields: ['branch_id']);

        if (! $warehouse) {
            $rules = $this->relaxCodeRulesForCreate($rules, 50);
        }

        $validated = $request->validate($rules);
        $descriptionParts = [];

        if (filled($validated['location'] ?? null)) {
            $descriptionParts[] = __('Location: :location', ['location' => $validated['location']]);
        }

        $notes = $validated['notes'] ?? $validated['description'] ?? null;

        if (filled($notes)) {
            $descriptionParts[] = __('Notes: :notes', ['notes' => $notes]);
        }

        $validated['description'] = implode("\n\n", $descriptionParts);
        $validated['branch_id'] = $effectiveBranchId;

        if (blank($validated['code'] ?? null)) {
            $validated['code'] = $this->resolveBranchScopedCode(
                $request,
                'name',
                Warehouse::class,
                $companyId,
                $effectiveBranchId,
                $warehouse?->id,
            );
        }

        return collect($validated)->only(['code', 'name', 'description', 'is_active', 'branch_id'])->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function warehouseFormMeta(string $formKey, ?int $companyId = null, ?int $branchId = null, ?Warehouse $warehouse = null): array
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $companyId !== null
            ? ['companyId' => $companyId, 'branchId' => $branchId]
            : $this->tenantIds();

        return [
            'formFields' => $this->formSettings->resolvedFields($formKey, $companyId, $branchId, $warehouse),
            'branches' => Branch::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'selectedBranchId' => $branchId,
        ];
    }

    protected function warehouseBalances(Warehouse $warehouse)
    {
        return InventoryItem::query()
            ->where('inventory_items.company_id', $warehouse->company_id)
            ->where('inventory_items.branch_id', $warehouse->branch_id)
            ->where('inventory_items.is_active', true)
            ->leftJoin('inventory_movements', function ($join) use ($warehouse) {
                $join->on('inventory_movements.inventory_item_id', '=', 'inventory_items.id')
                    ->where('inventory_movements.warehouse_id', $warehouse->id);
            })
            ->leftJoin('inventory_categories', 'inventory_categories.id', '=', 'inventory_items.inventory_category_id')
            ->select([
                'inventory_items.id',
                'inventory_items.public_id',
                'inventory_items.sku',
                'inventory_items.item_name',
                'inventory_items.reorder_level',
                'inventory_items.standard_cost',
                'inventory_categories.name as category_name',
                DB::raw('COALESCE(SUM(inventory_movements.quantity), 0) as balance'),
                DB::raw('COALESCE(SUM(inventory_movements.quantity * inventory_movements.unit_cost), 0) as ledger_value'),
            ])
            ->groupBy([
                'inventory_items.id',
                'inventory_items.public_id',
                'inventory_items.sku',
                'inventory_items.item_name',
                'inventory_items.reorder_level',
                'inventory_items.standard_cost',
                'inventory_categories.name',
            ])
            ->orderBy('inventory_items.item_name')
            ->get();
    }

    protected function hasOperationalHistory(Warehouse $warehouse): bool
    {
        $hasDocuments = StockReceipt::query()->where('warehouse_id', $warehouse->id)->exists()
            || StockIssue::query()
                ->where(fn ($query) => $query->where('warehouse_id', $warehouse->id)->orWhere('to_warehouse_id', $warehouse->id))
                ->exists()
            || StockAdjustment::query()->where('warehouse_id', $warehouse->id)->exists()
            || InventoryMovement::query()->where('warehouse_id', $warehouse->id)->exists()
            || $warehouse->managers()->exists();

        if ($hasDocuments) {
            return true;
        }

        return (float) InventoryMovement::query()
            ->where('warehouse_id', $warehouse->id)
            ->sum('quantity') !== 0.0;
    }
}

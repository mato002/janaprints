<?php

namespace App\Http\Controllers\Admin\Production;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use App\Models\Production\ProductBom;
use App\Support\Production\ProductBomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductBomController extends Controller
{
    use ScopesToTenant;

    public function __construct(
        protected ProductBomService $bomService,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', ProductBom::class);

        $boms = $this->scopeToTenant(
            ProductBom::query()
                ->with(['finishedItem', 'lines'])
                ->withCount('lines')
                ->latest('updated_at')
        )->paginate(config('platform.pagination.default', 15));

        return view('admin.production.boms.index', compact('boms'));
    }

    public function create(): View
    {
        $this->authorize('create', ProductBom::class);

        return view('admin.production.boms.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', ProductBom::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();
        [$header, $lines] = $this->validatePayload($request, $companyId, $branchId);

        try {
            $bom = $this->bomService->create($companyId, $branchId, (int) auth()->id(), $header, $lines);
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        return redirect()
            ->route('admin.production.boms.edit', $bom)
            ->with('status', __('Bill of materials created.'));
    }

    public function show(ProductBom $bom): RedirectResponse
    {
        $this->authorize('view', $bom);

        return redirect()->route('admin.production.boms.edit', $bom);
    }

    public function edit(ProductBom $bom): View
    {
        $this->authorize('update', $bom);

        $bom->load(['finishedItem', 'lines.inventoryItem.unitOfMeasure']);

        return view('admin.production.boms.edit', [
            ...$this->formMeta(),
            'bom' => $bom,
        ]);
    }

    public function update(Request $request, ProductBom $bom): RedirectResponse
    {
        $this->authorize('update', $bom);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();
        [$header, $lines] = $this->validatePayload($request, $companyId, $branchId);

        try {
            $this->bomService->update($bom, $header, $lines);
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        return back()->with('status', __('Bill of materials updated.'));
    }

    public function destroy(ProductBom $bom): RedirectResponse
    {
        $this->authorize('delete', $bom);

        $bom->delete();

        return redirect()
            ->route('admin.production.boms.index')
            ->with('status', __('Bill of materials removed.'));
    }

    /**
     * @return array{companyId: int, branchId: int}
     */
    protected function tenantIds(): array
    {
        return [
            'companyId' => (int) tenant()->companyId(),
            'branchId' => (int) tenant()->branchId(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(): array
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        return [
            'finishedItems' => InventoryItem::query()
                ->where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->where('is_active', true)
                ->orderBy('item_name')
                ->get(['id', 'sku', 'item_name']),
            'rawMaterials' => InventoryItem::query()
                ->where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->where('is_active', true)
                ->orderBy('sku')
                ->get(['id', 'sku', 'item_name']),
        ];
    }

    /**
     * @return array{0: array<string, mixed>, 1: list<array<string, mixed>>}
     */
    protected function validatePayload(Request $request, int $companyId, int $branchId): array
    {
        $header = $request->validate([
            'finished_item_id' => ['required', Rule::exists('inventory_items', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'name' => ['required', 'string', 'max:120'],
            'version' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $lines = $request->validate([
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.inventory_item_id' => ['required', Rule::exists('inventory_items', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'lines.*.quantity_per_unit' => ['required', 'numeric', 'min:0.0001'],
            'lines.*.waste_factor_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.notes' => ['nullable', 'string', 'max:255'],
        ])['lines'];

        return [$header, $lines];
    }
}

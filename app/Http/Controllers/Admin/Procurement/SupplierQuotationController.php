<?php

namespace App\Http\Controllers\Admin\Procurement;

use App\Enums\DocumentType;
use App\Enums\SupplierQuotationStatus;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Procurement\Concerns\ResolvesProcurementTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use App\Models\Procurement\SupplierQuotation;
use App\Rules\DateNotInThePast;
use App\Models\Procurement\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupplierQuotationController extends Controller
{
    use ResolvesProcurementTenant, ScopesToTenant;

    public function index(): View
    {
        $this->authorize('viewAny', SupplierQuotation::class);

        $quotations = $this->scopeToTenant(
            SupplierQuotation::query()->with(['vendor'])->latest('quotation_date')
        )->paginate(config('platform.pagination.default', 15));

        return view('admin.procurement.quotations.index', compact('quotations'));
    }

    public function create(): View
    {
        $this->authorize('create', SupplierQuotation::class);

        return view('admin.procurement.quotations.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', SupplierQuotation::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);
        $header = $this->validateHeader($request, $companyId);
        $lines = $this->validateLines($request, $companyId, $branchId);
        $subtotal = collect($lines)->sum(fn (array $line) => (float) $line['line_total']);

        $quotation = SupplierQuotation::query()->create([
            ...$header,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'quotation_number' => $this->nextNumber(DocumentType::SupplierQuotation, $companyId, $branchId),
            'status' => SupplierQuotationStatus::Draft,
            'subtotal' => $subtotal,
            'tax_amount' => 0,
            'total_amount' => $subtotal,
        ]);

        foreach ($lines as $line) {
            $quotation->items()->create($line);
        }

        return redirect()->route('admin.procurement.quotations.show', $quotation)->with('status', __('Supplier quotation created.'));
    }

    public function show(SupplierQuotation $quotation): View
    {
        $this->authorize('view', $quotation);

        $quotation->load(['items.inventoryItem', 'vendor']);

        return view('admin.procurement.quotations.show', compact('quotation'));
    }

    public function edit(SupplierQuotation $quotation): View
    {
        $this->authorize('update', $quotation);

        $quotation->load(['items.inventoryItem', 'vendor']);

        return view('admin.procurement.quotations.edit', array_merge(
            ['quotation' => $quotation],
            $this->formMeta(),
        ));
    }

    public function update(Request $request, SupplierQuotation $quotation): RedirectResponse
    {
        $this->authorize('update', $quotation);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);
        $header = $this->validateHeader($request, $companyId);
        $lines = $this->validateLines($request, $companyId, $branchId);
        $subtotal = collect($lines)->sum(fn (array $line) => (float) $line['line_total']);

        $quotation->update([
            ...$header,
            'subtotal' => $subtotal,
            'total_amount' => $subtotal,
        ]);

        $quotation->items()->delete();

        foreach ($lines as $line) {
            $quotation->items()->create($line);
        }

        return redirect()
            ->route('admin.procurement.quotations.show', $quotation)
            ->with('status', __('Supplier quotation updated.'));
    }

    public function destroy(SupplierQuotation $quotation): RedirectResponse
    {
        $this->authorize('delete', $quotation);

        $quotation->delete();

        return redirect()->route('admin.procurement.quotations.index')->with('status', __('Supplier quotation deleted.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateHeader(Request $request, int $companyId): array
    {
        return $request->validate([
            'vendor_id' => ['required', Rule::exists('vendors', 'id')->where('company_id', $companyId)],
            'quotation_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:quotation_date', new DateNotInThePast],
            'notes' => ['nullable', 'string'],
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function validateLines(Request $request, int $companyId, int $branchId): array
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id' => ['nullable', Rule::exists('inventory_items', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        return collect($validated['items'])->map(function (array $line) {
            $line['line_total'] = round((float) $line['quantity'] * (float) $line['unit_cost'], 2);

            return $line;
        })->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(): array
    {
        return [
            'vendors' => Vendor::query()->forTenant()->where('status', 'active')->orderBy('vendor_name')->get(),
            'items' => InventoryItem::query()->forTenant()->where('is_active', true)->orderBy('item_name')->get(),
        ];
    }
}

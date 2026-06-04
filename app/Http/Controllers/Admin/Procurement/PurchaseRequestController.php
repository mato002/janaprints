<?php

namespace App\Http\Controllers\Admin\Procurement;

use App\Enums\DocumentType;
use App\Enums\PurchaseRequestStatus;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Procurement\Concerns\ResolvesProcurementTenant;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Inventory\InventoryItem;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\Vendor;
use App\Support\Procurement\PurchaseRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseRequestController extends Controller
{
    use ResolvesProcurementTenant, ScopesToTenant;

    public function index(): View
    {
        $this->authorize('viewAny', PurchaseRequest::class);

        $requests = $this->scopeToTenant(
            PurchaseRequest::query()->with(['requester', 'department'])->latest()
        )->paginate(config('platform.pagination.default', 15));

        return view('admin.procurement.requests.index', compact('requests'));
    }

    public function create(): View
    {
        $this->authorize('create', PurchaseRequest::class);

        return view('admin.procurement.requests.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PurchaseRequest::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);
        $header = $this->validateHeader($request, $companyId);
        $lines = $this->validateLines($request, $companyId, $branchId);

        $purchaseRequest = PurchaseRequest::query()->create([
            ...$header,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'request_number' => $this->nextNumber(DocumentType::PurchaseRequest, $companyId, $branchId),
            'requested_by' => auth()->id(),
            'status' => PurchaseRequestStatus::Draft,
        ]);

        foreach ($lines as $line) {
            $purchaseRequest->items()->create($line);
        }

        return redirect()->route('admin.procurement.requests.show', $purchaseRequest)->with('status', __('Purchase request created.'));
    }

    public function show(PurchaseRequest $request): View
    {
        $this->authorize('view', $request);

        $request->load(['items.inventoryItem', 'requester', 'department', 'purchaseOrder']);

        return view('admin.procurement.requests.show', [
            'purchaseRequest' => $request,
            'vendors' => Vendor::query()->forTenant()->where('status', 'active')->orderBy('vendor_name')->get(),
        ]);
    }

    public function edit(PurchaseRequest $request): View
    {
        $this->authorize('update', $request);

        $request->load('items');

        return view('admin.procurement.requests.edit', array_merge(
            ['purchaseRequest' => $request],
            $this->formMeta(),
        ));
    }

    public function update(Request $request, PurchaseRequest $purchaseRequest): RedirectResponse
    {
        $this->authorize('update', $purchaseRequest);

        $header = $this->validateHeader($request, $purchaseRequest->company_id);
        $lines = $this->validateLines($request, $purchaseRequest->company_id, $purchaseRequest->branch_id);

        $purchaseRequest->update($header);
        $purchaseRequest->items()->delete();

        foreach ($lines as $line) {
            $purchaseRequest->items()->create($line);
        }

        return redirect()->route('admin.procurement.requests.show', $purchaseRequest)->with('status', __('Purchase request updated.'));
    }

    public function destroy(PurchaseRequest $request): RedirectResponse
    {
        $this->authorize('delete', $request);

        $request->delete();

        return redirect()->route('admin.procurement.requests.index')->with('status', __('Purchase request deleted.'));
    }

    public function submit(PurchaseRequest $request): RedirectResponse
    {
        $this->authorize('submit', $request);

        try {
            PurchaseRequestService::submit($request);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Purchase request submitted.'));
    }

    public function approve(PurchaseRequest $request): RedirectResponse
    {
        $this->authorize('approve', $request);

        try {
            PurchaseRequestService::approve($request);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Purchase request approved.'));
    }

    public function convert(Request $httpRequest, PurchaseRequest $request): RedirectResponse
    {
        $this->authorize('convert', $request);

        $validated = $httpRequest->validate([
            'vendor_id' => ['required', Rule::exists('vendors', 'id')->where('company_id', $request->company_id)],
        ]);

        try {
            $order = PurchaseRequestService::convertToPurchaseOrder(
                $request,
                (int) $validated['vendor_id'],
                (int) auth()->id(),
                $this->nextNumber(DocumentType::PurchaseOrder, $request->company_id, $request->branch_id),
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('admin.procurement.orders.show', $order)->with('status', __('Purchase order created from request.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateHeader(Request $request, int $companyId): array
    {
        return $request->validate([
            'department_id' => ['nullable', Rule::exists('departments', 'id')->where('company_id', $companyId)],
            'required_date' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
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
            'items.*.estimated_unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        return collect($validated['items'])->map(function (array $line) {
            $line['line_total'] = round((float) $line['quantity'] * (float) $line['estimated_unit_cost'], 2);

            return $line;
        })->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(): array
    {
        return [
            'departments' => Department::query()->forCompany(tenant()->companyId())->orderBy('name')->get(),
            'items' => InventoryItem::query()->forTenant()->where('is_active', true)->orderBy('item_name')->get(),
        ];
    }
}

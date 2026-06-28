<?php

namespace App\Http\Controllers\Admin\Procurement;

use App\Enums\DocumentType;
use App\Enums\VendorStatus;
use App\Enums\VendorType;
use App\Http\Controllers\Admin\Concerns\HandlesModalFormResponses;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Procurement\Concerns\ResolvesProcurementTenant;
use App\Http\Controllers\Controller;
use App\Models\Procurement\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VendorController extends Controller
{
    use HandlesModalFormResponses, ResolvesProcurementTenant, ScopesToTenant;

    public function index(): View
    {
        $this->authorize('viewAny', Vendor::class);

        $vendors = $this->scopeToTenant(
            Vendor::query()->latest('vendor_name')
        )->paginate(config('platform.pagination.default', 15));

        return view('admin.procurement.vendors.index', compact('vendors'));
    }

    public function create(): View
    {
        $this->authorize('create', Vendor::class);

        return view('admin.procurement.vendors.create', [
            'types' => VendorType::cases(),
            'statuses' => VendorStatus::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse|Response
    {
        $this->authorize('create', Vendor::class);

        ['companyId' => $companyId] = $this->tenantIds($request);
        $data = $this->validateVendor($request);

        $vendor = Vendor::query()->create([
            ...$data,
            'company_id' => $companyId,
            'vendor_code' => $this->nextNumber(DocumentType::Vendor, $companyId),
        ]);

        return $this->modalOrRedirect(
            __('Vendor created.'),
            redirect()->route('admin.procurement.vendors.show', $vendor),
        );
    }

    public function show(Vendor $vendor): View
    {
        $this->authorize('view', $vendor);

        $vendor->load(['contacts']);

        $logService = app(\App\Support\Communications\CommunicationLogService::class);
        $communicationTimeline = auth()->user()->can('communications.logs.view')
            ? $logService->forEntity('vendor', $vendor->id, $vendor->company_id)
            : collect();
        $emailTimeline = auth()->user()->can('communications.logs.view')
            ? $logService->forEntity('vendor', $vendor->id, $vendor->company_id, 15, \App\Enums\CommunicationLogChannel::Email)
            : collect();

        return view('admin.procurement.vendors.show', compact('vendor', 'communicationTimeline', 'emailTimeline'));
    }

    public function edit(Vendor $vendor): View
    {
        $this->authorize('update', $vendor);

        return view('admin.procurement.vendors.edit', [
            'vendor' => $vendor,
            'types' => VendorType::cases(),
            'statuses' => VendorStatus::cases(),
        ]);
    }

    public function update(Request $request, Vendor $vendor): RedirectResponse|Response
    {
        $this->authorize('update', $vendor);

        $vendor->update($this->validateVendor($request));

        return $this->modalOrRedirect(
            __('Vendor updated.'),
            redirect()->route('admin.procurement.vendors.show', $vendor),
        );
    }

    public function destroy(Vendor $vendor): RedirectResponse
    {
        $this->authorize('delete', $vendor);

        $vendor->delete();

        return redirect()->route('admin.procurement.vendors.index')->with('status', __('Vendor deleted.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateVendor(Request $request): array
    {
        $data = $request->validate([
            'vendor_name' => ['required', 'string', 'max:255'],
            'vendor_type' => ['required', Rule::enum(VendorType::class)],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'kra_pin' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(VendorStatus::class)],
            'notes' => ['nullable', 'string'],
        ]);

        $data['is_production_vendor'] = $request->boolean('is_production_vendor');

        return $data;
    }
}

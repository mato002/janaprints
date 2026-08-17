<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Enums\CustomerPrintSpecificationStatus;
use App\Enums\FulfilmentMethod;
use App\Enums\SalesOrderBillingType;
use App\Http\Controllers\Admin\Concerns\HandlesModalFormResponses;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerPrintSpecification;
use App\Models\Crm\CustomerProductSerialProfile;
use App\Models\Inventory\InventoryItem;
use App\Support\Crm\CustomerArtworkService;
use App\Support\Crm\CustomerArtworkTypeCatalog;
use App\Support\Crm\CustomerPrintSpecificationLifecycleService;
use App\Support\Crm\CustomerPrintSpecificationService;
use App\Support\Crm\CustomerPrintSpecificationWorkspaceService;
use App\Support\Sales\ReturnsToSalesDesk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerPrintSpecificationController extends Controller
{
    use HandlesModalFormResponses, ReturnsToSalesDesk, ScopesToTenant;

    public function __construct(
        protected CustomerPrintSpecificationService $specifications,
        protected CustomerArtworkService $artworks,
        protected CustomerPrintSpecificationWorkspaceService $workspace,
        protected CustomerPrintSpecificationLifecycleService $lifecycle,
    ) {}

    public function show(Request $request, Customer $customer, CustomerPrintSpecification $printSpecification): View
    {
        $this->authorize('view', $customer);
        $this->assertSpecificationBelongsToCustomer($customer, $printSpecification);

        $workspace = $this->workspace->build(
            $printSpecification,
            $request->query('tab'),
        );

        return view('admin.crm.customers.print-specifications.show', [
            'customer' => $customer,
            'workspace' => $workspace,
            'specification' => $printSpecification,
        ]);
    }

    public function transition(Request $request, Customer $customer, CustomerPrintSpecification $printSpecification): RedirectResponse|Response
    {
        $this->authorize('update', $customer);
        $this->assertSpecificationBelongsToCustomer($customer, $printSpecification);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(CustomerPrintSpecificationStatus::class)],
        ]);

        $status = CustomerPrintSpecificationStatus::from($validated['status']);
        $this->lifecycle->transition($printSpecification, $status, (int) auth()->id());

        return $this->modalOrRedirect(
            __('Print specification status updated.'),
            redirect()->route('admin.crm.customers.print-specifications.show', [
                'customer' => $customer,
                'printSpecification' => $printSpecification,
            ]),
        );
    }

    public function create(Customer $customer): View
    {
        $this->authorize('update', $customer);

        return view('admin.crm.customers.print-specifications.create', [
            'customer' => $customer,
            'statuses' => CustomerPrintSpecificationStatus::cases(),
            'billingTypes' => SalesOrderBillingType::cases(),
            'fulfilmentMethods' => FulfilmentMethod::cases(),
            'artworkTypes' => app(CustomerArtworkTypeCatalog::class)->optionsForCompany((int) $customer->company_id),
        ]);
    }

    public function store(Request $request, Customer $customer): RedirectResponse|Response
    {
        $this->authorize('update', $customer);

        $validated = $this->validateSpecification($request, $customer);
        if (! empty($validated['inventory_item_id'])) {
            $this->assertInventoryItemForTenant((int) $validated['inventory_item_id']);
        }

        $spec = $this->specifications->create($customer, $validated, (int) auth()->id());

        if ($request->hasFile('artwork_file')) {
            $this->artworks->uploadVersionForSpecification(
                $spec,
                $request->file('artwork_file'),
                (int) auth()->id(),
                $validated['artwork_change_notes'] ?? null,
                $validated['artwork_type'] ?? app(CustomerArtworkTypeCatalog::class)->defaultCode(),
            );
        }

        if (! empty($validated['serial_prefix'])) {
            $this->saveSerialProfile($customer, $spec, $validated);
        }

        return $this->modalOrRedirect(
            __('Print specification created.'),
            $this->redirectAfterSpecificationSave($request, $customer, $spec),
        );
    }

    public function edit(Customer $customer, CustomerPrintSpecification $printSpecification): View
    {
        $this->authorize('update', $customer);
        $this->assertSpecificationBelongsToCustomer($customer, $printSpecification);

        $printSpecification->load([
            'inventoryItem:id,item_name,sku,uses_serial_numbers,serial_prefix,serial_padding_length',
            'artworkVersions.uploader:id,name',
            'activeArtworkVersion',
        ]);

        $serialProfile = CustomerProductSerialProfile::query()
            ->where('customer_id', $customer->id)
            ->where('inventory_item_id', $printSpecification->inventory_item_id)
            ->first();

        return view('admin.crm.customers.print-specifications.edit', [
            'customer' => $customer,
            'specification' => $printSpecification,
            'serialProfile' => $serialProfile,
            'serialSummary' => $this->specifications->serialSummary($printSpecification),
            'liveReferenceWarnings' => $this->lifecycle->liveReferenceWarnings($printSpecification),
            'hasOperationalUsage' => $printSpecification->hasOperationalUsage(),
            'statuses' => CustomerPrintSpecificationStatus::cases(),
            'billingTypes' => SalesOrderBillingType::cases(),
            'fulfilmentMethods' => FulfilmentMethod::cases(),
            'artworkTypes' => app(CustomerArtworkTypeCatalog::class)->optionsForCompany((int) $customer->company_id),
        ]);
    }

    public function update(Request $request, Customer $customer, CustomerPrintSpecification $printSpecification): RedirectResponse|Response
    {
        $this->authorize('update', $customer);
        $this->assertSpecificationBelongsToCustomer($customer, $printSpecification);
        $this->lifecycle->assertEditable($printSpecification);

        $validated = $this->validateSpecification($request, $customer, $printSpecification);
        if (! empty($validated['inventory_item_id'])) {
            $this->assertInventoryItemForTenant((int) $validated['inventory_item_id']);
        }

        $this->specifications->update($printSpecification, $validated, (int) auth()->id());

        if (! empty($validated['serial_prefix'])) {
            $this->saveSerialProfile($customer, $printSpecification->fresh(), $validated);
        }

        return $this->modalOrRedirect(
            __('Print specification updated.'),
            $this->wantsSalesDeskReturn($request)
                ? redirect()->route('admin.sales.desk', [
                    'customer' => $customer->getRouteKey(),
                    'specification' => $printSpecification->id,
                    'step' => 2,
                ])
                : redirect()->route('admin.crm.customers.show', ['customer' => $customer, 'tab' => 'print-specifications']),
        );
    }

    public function uploadArtwork(Request $request, Customer $customer, CustomerPrintSpecification $printSpecification): RedirectResponse|Response
    {
        $this->authorize('update', $customer);
        $this->assertSpecificationBelongsToCustomer($customer, $printSpecification);
        $this->lifecycle->assertEditable($printSpecification);

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:20480', 'mimes:jpg,jpeg,png,webp,pdf'],
            'change_notes' => ['nullable', 'string', 'max:2000'],
            'artwork_type' => app(CustomerArtworkTypeCatalog::class)->validationRules((int) $customer->company_id),
        ]);

        $this->artworks->uploadVersionForSpecification(
            $printSpecification,
            $request->file('file'),
            (int) auth()->id(),
            $validated['change_notes'] ?? null,
            $validated['artwork_type'] ?? app(CustomerArtworkTypeCatalog::class)->defaultCode(),
        );

        return $this->modalOrRedirect(
            __('Artwork version uploaded.'),
            $this->wantsSalesDeskReturn($request)
                ? redirect()->route('admin.sales.desk', [
                    'customer' => $customer->getRouteKey(),
                    'specification' => $printSpecification->id,
                    'step' => 2,
                ])
                : redirect()->route('admin.crm.customers.print-specifications.edit', [$customer, $printSpecification]),
        );
    }

    public function saveSerialProfileFromSpec(Request $request, Customer $customer, CustomerPrintSpecification $printSpecification): RedirectResponse|Response
    {
        $this->authorize('update', $customer);
        $this->assertSpecificationBelongsToCustomer($customer, $printSpecification);
        $this->lifecycle->assertEditable($printSpecification);

        abort_unless($printSpecification->inventory_item_id, 422);

        $validated = $request->validate([
            'serial_prefix' => ['required', 'string', 'max:30'],
            'serial_padding_length' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        CustomerProductSerialProfile::query()->updateOrCreate(
            [
                'company_id' => $customer->company_id,
                'branch_id' => $customer->branch_id,
                'customer_id' => $customer->id,
                'inventory_item_id' => $printSpecification->inventory_item_id,
            ],
            [
                'serial_prefix' => $validated['serial_prefix'],
                'serial_padding_length' => $validated['serial_padding_length'],
            ],
        );

        return $this->modalOrRedirect(
            __('Serial numbering profile saved.'),
            redirect()->route('admin.crm.customers.print-specifications.edit', [$customer, $printSpecification]),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateSpecification(Request $request, Customer $customer, ?CustomerPrintSpecification $existing = null): array
    {
        return $request->validate(array_merge([
            'product_name' => ['required', 'string', 'max:255'],
            'inventory_item_id' => ['nullable', 'integer', 'exists:inventory_items,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(CustomerPrintSpecificationStatus::class)],
            'production_notes' => ['nullable', 'string'],
            'commercial_notes' => ['nullable', 'string'],
            'customer_instructions' => ['nullable', 'string'],
            'default_quantity' => ['nullable', 'numeric', 'min:0'],
            'default_unit_price' => ['nullable', 'numeric', 'min:0'],
            'default_billing_type' => ['nullable', Rule::enum(SalesOrderBillingType::class)],
            'default_fulfilment_method' => ['nullable', Rule::enum(FulfilmentMethod::class)],
            'artwork_file' => [$existing ? 'nullable' : 'nullable', 'file', 'max:20480', 'mimes:jpg,jpeg,png,webp,pdf'],
            'artwork_type' => app(CustomerArtworkTypeCatalog::class)->validationRules((int) $customer->company_id),
            'artwork_change_notes' => ['nullable', 'string', 'max:2000'],
            'serial_prefix' => ['nullable', 'string', 'max:30'],
            'serial_padding_length' => ['nullable', 'integer', 'min:1', 'max:12'],
        ], app(\App\Support\Production\PrintSpecificationJobFields::class)->validationRules()));
    }

    protected function assertInventoryItemForTenant(int $inventoryItemId): void
    {
        InventoryItem::query()->forTenant()->whereKey($inventoryItemId)->firstOrFail();
    }

    protected function assertSpecificationBelongsToCustomer(Customer $customer, CustomerPrintSpecification $spec): void
    {
        abort_unless($spec->customer_id === $customer->id, 404);
    }

    protected function redirectAfterSpecificationSave(
        Request $request,
        Customer $customer,
        CustomerPrintSpecification $spec,
    ): RedirectResponse {
        if ($this->wantsSalesOrderReturn($request)) {
            return redirect()->route('admin.sales-orders.create', [
                'tab' => 'direct',
                'customer_id' => $customer->id,
                'print_specification_id' => $spec->id,
            ]);
        }

        if ($this->wantsSalesDeskReturn($request)) {
            return redirect()->route('admin.sales.desk', [
                'customer' => $customer->getRouteKey(),
                'specification' => $spec->id,
                'step' => 3,
            ]);
        }

        return redirect()->route('admin.crm.customers.show', [
            'customer' => $customer,
            'tab' => 'print-specifications',
        ]);
    }

    protected function wantsSalesOrderReturn(Request $request): bool
    {
        return $request->input('from') === 'sales-order';
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    protected function saveSerialProfile(Customer $customer, CustomerPrintSpecification $spec, array $validated): void
    {
        if (empty($validated['serial_prefix']) || empty($validated['serial_padding_length']) || ! $spec->inventory_item_id) {
            return;
        }

        CustomerProductSerialProfile::query()->updateOrCreate(
            [
                'company_id' => $customer->company_id,
                'branch_id' => $customer->branch_id,
                'customer_id' => $customer->id,
                'inventory_item_id' => $spec->inventory_item_id,
            ],
            [
                'serial_prefix' => $validated['serial_prefix'],
                'serial_padding_length' => $validated['serial_padding_length'],
            ],
        );
    }
}

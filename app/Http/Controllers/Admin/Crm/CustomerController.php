<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Http\Controllers\Admin\Concerns\HandlesFormCustomFields;
use App\Http\Controllers\Admin\Concerns\HandlesModalFormResponses;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Crm\Concerns\ResolvesCrmTenant;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerSegment;
use App\Support\Crm\CustomerOperationalGuard;
use App\Support\Platform\FormSettingsService;
use App\Support\Platform\FormStatusOptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    use HandlesFormCustomFields, HandlesModalFormResponses, ResolvesCrmTenant, ScopesToTenant;

    public function __construct(
        protected FormSettingsService $formSettings,
        protected FormStatusOptionService $statusOptions,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Customer::class);

        $customers = $this->scopeToTenant(
            Customer::query()->with(['branch', 'segments', 'portalUser'])
        )->latest()->paginate(15);

        return view('admin.crm.customers.index', compact('customers'));
    }

    public function export(Request $request, string $format = 'csv'): StreamedResponse
    {
        $this->authorize('viewAny', Customer::class);

        if (! in_array($format, ['csv', 'excel', 'pdf'], true)) {
            $format = 'csv';
        }

        $query = $this->scopeToTenant(
            Customer::query()->with('branch')
        );

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($builder) use ($search) {
                $builder->where('company_name', 'like', "%{$search}%")
                    ->orWhere('customer_code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderByDesc('created_at')->get();

        $headers = [__('Customer'), __('Code'), __('Branch'), __('Phone'), __('Email'), __('Status')];
        $rows = $customers->map(fn (Customer $customer) => [
            $customer->company_name,
            $customer->customer_code,
            $customer->branch?->name ?? '',
            $customer->phone ?? '',
            $customer->email ?? '',
            $customer->status?->value ?? '',
        ])->all();

        return app(\App\Support\Export\TabularExportWriter::class)->download(
            $format,
            'customers-'.now()->format('Y-m-d'),
            $headers,
            $rows,
            __('Customers'),
        );
    }

    public function create(): View
    {
        $this->authorize('create', Customer::class);

        return view('admin.crm.customers.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse|Response
    {
        $this->authorize('create', Customer::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);
        $data = $this->validateCustomer($request, null, $companyId, $branchId);
        $data = $this->formSettings->applyDefaults('customer', $data, $companyId, $branchId);
        [$data, $customData] = $this->partitionCustomFields('customer', $data, $companyId, $branchId);

        $customer = Customer::query()->create([
            ...$data,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'customer_code' => $this->nextCustomerCode($companyId),
        ]);

        if (! empty($data['segment_ids'])) {
            $customer->segments()->sync($data['segment_ids']);
        }

        $this->syncCustomFields($customer, 'customer', $customData, $companyId);

        return $this->modalOrRedirect(
            __('Customer created.'),
            redirect()->route('admin.crm.customers.show', $customer),
        );
    }

    public function show(Request $request, Customer $customer): View
    {
        $this->authorize('view', $customer);

        $customer->load(['contacts', 'customerNotes.user', 'files.uploader', 'activities.user', 'segments', 'branch', 'portalUser.sessions']);

        $logService = app(\App\Support\Communications\CommunicationLogService::class);
        $communicationTimeline = auth()->user()->can('communications.logs.view')
            ? $logService->forEntity('customer', $customer->id, $customer->company_id)
            : collect();
        $whatsappTimeline = auth()->user()->can('communications.logs.view')
            ? $logService->forEntity('customer', $customer->id, $customer->company_id, 15, \App\Enums\CommunicationLogChannel::WhatsApp)
            : collect();
        $whatsappConversations = auth()->user()->can('communications.whatsapp.view')
            ? app(\App\Support\Communications\Whatsapp\WhatsappConversationService::class)
                ->forCustomer($customer->company_id, $customer->id)
            : collect();
        $emailTimeline = auth()->user()->can('communications.logs.view')
            ? $logService->forEntity('customer', $customer->id, $customer->company_id, 15, \App\Enums\CommunicationLogChannel::Email)
            : collect();
        $inboxConversations = auth()->user()->can('communications.inbox.view')
            ? app(\App\Support\Communications\Inbox\InboxConversationService::class)
                ->forCustomer($customer->company_id, $customer->id)
            : collect();

        return view('admin.crm.customers.show', compact(
            'customer',
            'communicationTimeline',
            'whatsappTimeline',
            'whatsappConversations',
            'emailTimeline',
            'inboxConversations',
        ));
    }

    public function edit(Customer $customer): View
    {
        $this->authorize('update', $customer);

        return view('admin.crm.customers.edit', array_merge(
            ['customer' => $customer],
            $this->formMeta($customer),
        ));
    }

    public function update(Request $request, Customer $customer): RedirectResponse|Response
    {
        $this->authorize('update', $customer);

        $data = $this->validateCustomer($request, $customer, $customer->company_id, $customer->branch_id);
        [$data, $customData] = $this->partitionCustomFields('customer', $data, $customer->company_id, $customer->branch_id);
        $customer->update($data);
        $customer->segments()->sync($data['segment_ids'] ?? []);
        $this->syncCustomFields($customer, 'customer', $customData, $customer->company_id);

        return $this->modalOrRedirect(
            __('Customer updated.'),
            redirect()->route('admin.crm.customers.show', $customer),
        );
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorize('delete', $customer);

        if (app(CustomerOperationalGuard::class)->hasOperationalHistory($customer)) {
            return back()->withErrors([
                'customer' => __('Customer has operational history and cannot be deleted. Deactivate the account instead.'),
            ]);
        }

        $customer->delete();

        return redirect()->route('admin.crm.customers.index')->with('status', __('Customer deleted.'));
    }

    public function deactivate(Customer $customer): RedirectResponse
    {
        $this->authorize('deactivate', $customer);

        $customer->update(['status' => CustomerStatus::Inactive]);

        return redirect()->route('admin.crm.customers.show', $customer)->with('status', __('Customer deactivated.'));
    }

    protected function validateCustomer(
        Request $request,
        ?Customer $customer = null,
        ?int $companyId = null,
        ?int $branchId = null,
    ): array {
        $companyId ??= $customer?->company_id ?? tenant()->companyId();
        $branchId ??= $customer?->branch_id ?? tenant()->branchId();

        return $this->formSettings->validateRequest($request, 'customer', [
            'customer_type' => [Rule::enum(CustomerType::class)],
            'company_name' => ['string', 'max:255'],
            'contact_person' => ['string', 'max:255'],
            'phone' => ['string', 'max:50'],
            'alternative_phone' => ['string', 'max:50'],
            'email' => ['email'],
            'kra_pin' => ['string', 'max:50'],
            'physical_address' => ['string'],
            'postal_address' => ['string'],
            'city' => ['string', 'max:100'],
            'website' => ['string', 'max:255'],
            'credit_limit' => ['numeric', 'min:0'],
            'payment_terms' => ['string', 'max:100'],
            'status' => $this->statusOptions->validationRules('customer', $companyId, $branchId),
            'notes' => ['string'],
            'segment_ids' => ['array'],
            'segment_ids.*' => ['exists:customer_segments,id'],
            'company_id' => ['sometimes', 'exists:companies,id'],
            'branch_id' => ['sometimes', 'exists:branches,id'],
        ], $companyId, $branchId, serverProvidedFields: ['company_id', 'branch_id']);
    }

    protected function formMeta(?Customer $customer = null): array
    {
        $companyId = $customer?->company_id ?? tenant()->companyId() ?? auth()->user()->company_id;

        $branchId = $customer?->branch_id ?? tenant()->branchId();

        return [
            'formFields' => $this->formSettings->resolvedFields('customer', $companyId, $branchId, $customer),
            'companies' => auth()->user()->hasRole('Super Admin')
                ? Company::query()->where('is_active', true)->orderBy('name')->get()
                : Company::query()->where('id', auth()->user()->company_id)->get(),
            'branches' => Branch::query()->where('company_id', $companyId)->where('is_active', true)->get(),
            'segments' => CustomerSegment::query()->where('company_id', $companyId)->where('is_active', true)->get(),
            'types' => CustomerType::cases(),
        ];
    }
}

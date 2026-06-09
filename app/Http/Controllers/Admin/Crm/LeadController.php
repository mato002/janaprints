<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Enums\LeadStatus;
use App\Http\Controllers\Admin\Concerns\HandlesFormCustomFields;
use App\Http\Controllers\Admin\Concerns\HandlesModalFormResponses;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Crm\Concerns\ResolvesCrmTenant;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadSource;
use App\Models\Crm\LeadStage;
use App\Models\User;
use App\Services\Crm\LeadQuotationService;
use App\Support\Crm\LeadConversionService;
use App\Support\Crm\LeadOperationalGuard;
use App\Support\Platform\FormSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeadController extends Controller
{
    use HandlesFormCustomFields, HandlesModalFormResponses, ResolvesCrmTenant, ScopesToTenant;

    public function __construct(
        protected FormSettingsService $formSettings,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Lead::class);

        $leads = $this->scopeToTenant(
            Lead::query()->with(['stage', 'leadSource', 'assignee', 'branch'])
        )->latest()->paginate(15);

        return view('admin.crm.leads.index', compact('leads'));
    }

    public function create(): View
    {
        $this->authorize('create', Lead::class);

        return view('admin.crm.leads.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse|Response
    {
        $this->authorize('create', Lead::class);

        $data = $this->validateLead($request);
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);
        $data = $this->formSettings->applyDefaults('lead', $data, $companyId, $branchId);
        [$data, $customData] = $this->partitionCustomFields('lead', $data, $companyId, $branchId);

        $lead = Lead::query()->create([
            ...collect($data)->except(['company_id', 'branch_id'])->toArray(),
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $this->syncCustomFields($lead, 'lead', $customData, $companyId);

        return $this->modalOrRedirect(
            __('Lead created.'),
            redirect()->route('admin.crm.leads.show', $lead),
        );
    }

    public function show(Lead $lead): View
    {
        $this->authorize('view', $lead);

        $lead->load([
            'stage',
            'leadSource',
            'assignee',
            'customer',
            'followUps.assignee',
            'activities.user',
            'quotations' => fn ($query) => $query->latest('quotation_date')->limit(10),
        ]);

        return view('admin.crm.leads.show', compact('lead'));
    }

    public function edit(Lead $lead): View
    {
        $this->authorize('update', $lead);

        return view('admin.crm.leads.edit', array_merge(['lead' => $lead], $this->formMeta($lead)));
    }

    public function update(Request $request, Lead $lead): RedirectResponse|Response
    {
        $this->authorize('update', $lead);

        $data = $this->validateLead($request, $lead);
        [$data, $customData] = $this->partitionCustomFields('lead', $data, $lead->company_id, $lead->branch_id);
        $lead->update($data);
        $this->syncCustomFields($lead, 'lead', $customData, $lead->company_id);

        return $this->modalOrRedirect(
            __('Lead updated.'),
            redirect()->route('admin.crm.leads.show', $lead),
        );
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $this->authorize('delete', $lead);

        if (app(LeadOperationalGuard::class)->hasDownstreamLinks($lead)) {
            return back()->withErrors([
                'lead' => __('Lead has downstream links and cannot be deleted.'),
            ]);
        }

        $lead->delete();

        return redirect()->route('admin.crm.leads.index')->with('status', __('Lead deleted.'));
    }

    public function convert(Lead $lead, LeadConversionService $conversion): RedirectResponse
    {
        $this->authorize('convert', $lead);

        if ($lead->customer_id && ($customer = Customer::query()->forTenant()->find($lead->customer_id))) {
            return redirect()
                ->route('admin.crm.customers.show', $customer)
                ->with('status', __('Lead is already linked to a customer.'));
        }

        $customer = $conversion->convert($lead);

        return redirect()
            ->route('admin.crm.customers.show', $customer)
            ->with('status', __('Lead converted to customer.'));
    }

    public function markLost(Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        $lead->update(['status' => LeadStatus::Lost]);

        return redirect()->route('admin.crm.leads.show', $lead)->with('status', __('Lead marked as lost.'));
    }

    public function createQuotation(Lead $lead, LeadQuotationService $leadQuotations): RedirectResponse
    {
        $this->authorize('quote', $lead);

        try {
            $customer = $leadQuotations->resolveCustomer($lead, auth()->user());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return redirect()->route('admin.quotations.create', [
            'customer_id' => $customer->id,
            'lead_id' => $lead->id,
        ]);
    }

    public function quickQuotation(Lead $lead, LeadQuotationService $leadQuotations): RedirectResponse
    {
        $this->authorize('quote', $lead);

        try {
            $quotation = $leadQuotations->createDraftQuotation($lead, auth()->user());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return redirect()
            ->route('admin.quotations.show', $quotation)
            ->with('status', __('Draft quotation created from lead.'));
    }

    protected function validateLead(Request $request, ?Lead $lead = null): array
    {
        $companyId = $lead?->company_id ?? tenant()->companyId() ?? auth()->user()->company_id;
        $branchId = $lead?->branch_id ?? tenant()->branchId();

        $rules = $this->formSettings->mergeValidationRules('lead', [
            'lead_source_id' => [Rule::exists('lead_sources', 'id')->where('company_id', $companyId)],
            'assigned_to' => ['exists:users,id'],
            'customer_id' => [Rule::exists('customers', 'id')->where('company_id', $companyId)],
            'stage_id' => [Rule::exists('lead_stages', 'id')->where('company_id', $companyId)],
            'lead_name' => ['string', 'max:255'],
            'company_name' => ['string', 'max:255'],
            'phone' => ['string', 'max:50'],
            'email' => ['email'],
            'estimated_value' => ['numeric', 'min:0'],
            'probability' => ['integer', 'min:0', 'max:100'],
            'expected_close_date' => ['date'],
            'status' => [Rule::enum(LeadStatus::class)],
            'notes' => ['string'],
            'company_id' => ['sometimes', 'exists:companies,id'],
            'branch_id' => ['sometimes', 'exists:branches,id'],
        ], $companyId, $branchId);

        return $request->validate($rules);
    }

    protected function formMeta(?Lead $lead = null): array
    {
        $companyId = $lead?->company_id ?? tenant()->companyId() ?? auth()->user()->company_id;

        $branchId = $lead?->branch_id ?? tenant()->branchId();

        return [
            'formFields' => $this->formSettings->resolvedFields('lead', $companyId, $branchId, $lead),
            'companies' => auth()->user()->hasRole('Super Admin')
                ? Company::query()->where('is_active', true)->orderBy('name')->get()
                : Company::query()->where('id', auth()->user()->company_id)->get(),
            'branches' => Branch::query()->where('company_id', $companyId)->get(),
            'sources' => LeadSource::query()->where('company_id', $companyId)->where('is_active', true)->get(),
            'stages' => LeadStage::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('sort_order')->get(),
            'customers' => Customer::query()->forTenant()->orderBy('company_name')->get(),
            'users' => User::query()->when(! auth()->user()->hasRole('Super Admin'), fn ($q) => $q->where('company_id', $companyId))->get(),
            'statuses' => LeadStatus::cases(),
        ];
    }
}

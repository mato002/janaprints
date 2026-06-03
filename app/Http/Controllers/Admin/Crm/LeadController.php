<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Enums\LeadStatus;
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
use App\Support\Platform\FormSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeadController extends Controller
{
    use ResolvesCrmTenant, ScopesToTenant;

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

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Lead::class);

        $data = $this->validateLead($request);
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);
        $data = $this->formSettings->applyDefaults('lead', $data, $companyId, $branchId);

        $lead = Lead::query()->create([
            ...collect($data)->except(['company_id', 'branch_id'])->toArray(),
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        return redirect()->route('admin.crm.leads.show', $lead)->with('status', __('Lead created.'));
    }

    public function show(Lead $lead): View
    {
        $this->authorize('view', $lead);

        $lead->load(['stage', 'leadSource', 'assignee', 'customer', 'followUps.assignee', 'activities.user']);

        return view('admin.crm.leads.show', compact('lead'));
    }

    public function edit(Lead $lead): View
    {
        $this->authorize('update', $lead);

        return view('admin.crm.leads.edit', array_merge(['lead' => $lead], $this->formMeta($lead)));
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        $lead->update($this->validateLead($request, $lead));

        return redirect()->route('admin.crm.leads.show', $lead)->with('status', __('Lead updated.'));
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $this->authorize('delete', $lead);

        $lead->delete();

        return redirect()->route('admin.crm.leads.index')->with('status', __('Lead deleted.'));
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
            'formFields' => $this->formSettings->resolvedFields('lead', $companyId, $branchId),
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

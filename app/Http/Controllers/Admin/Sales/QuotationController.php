<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Enums\DocumentType;
use App\Enums\QuotationStatus;
use App\Http\Controllers\Admin\Concerns\HandlesFormCustomFields;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Crm\Concerns\ResolvesCrmTenant;
use App\Http\Controllers\Admin\Sales\Concerns\ManagesQuotationItems;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Sales\Quotation;
use App\Enums\ApprovalRuleType;
use App\Enums\DocumentModule;
use App\Support\Platform\ApprovalDelegationService;
use App\Support\Platform\FormSettingsService;
use App\Support\Platform\NumberingService;
use App\Enums\WorkflowRuleTrigger;
use App\Support\Governance\WorkflowRulesService;
use App\Services\Crm\LeadQuotationService;
use App\Support\QuotationConversionService;
use App\Support\QuotationRevisionService;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuotationController extends Controller
{
    use HandlesFormCustomFields, ManagesQuotationItems, ResolvesCrmTenant, ScopesToTenant;

    public function __construct(
        protected FormSettingsService $formSettings,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Quotation::class);

        $quotations = $this->scopeToTenant(
            Quotation::query()->with(['customer', 'branch', 'preparer'])
        )->latest('quotation_date')->paginate(15);

        return view('admin.sales.quotations.index', compact('quotations'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Quotation::class);

        $presetCustomerId = null;
        $presetLeadId = null;

        if ($request->filled('lead_id')) {
            $lead = Lead::query()->forTenant()->find($request->integer('lead_id'));
            abort_unless($lead, 404);
            $this->authorize('view', $lead);
            $presetLeadId = $lead->id;

            if ($request->filled('customer_id')) {
                $customer = Customer::query()->forTenant()->find($request->integer('customer_id'));
                abort_unless($customer, 404);
                $this->authorize('view', $customer);
                $presetCustomerId = $customer->id;
            } elseif ($user = auth()->user()) {
                try {
                    $customer = app(LeadQuotationService::class)->resolveCustomer($lead, $user);
                    $presetCustomerId = $customer->id;
                } catch (ValidationException $exception) {
                    return redirect()
                        ->route('admin.crm.leads.show', $lead)
                        ->withErrors($exception->errors());
                }
            }
        } elseif ($request->filled('customer_id')) {
            $customer = Customer::query()->forTenant()->find($request->integer('customer_id'));
            abort_unless($customer, 404);
            $this->authorize('view', $customer);
            $presetCustomerId = $customer->id;
        }

        return view('admin.sales.quotations.create', array_merge($this->formMeta(), [
            'presetCustomerId' => $presetCustomerId,
            'presetLeadId' => $presetLeadId,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Quotation::class);

        $header = $this->validateHeader($request);
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);
        [$header, $customData] = $this->partitionCustomFields('quotation', $header, $companyId, $branchId);
        ['items' => $items, 'totals' => $totals] = $this->validatedItems($request);

        $quotation = Quotation::query()->create([
            ...$header,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'quotation_number' => $this->nextQuotationNumber($companyId, $branchId),
            'prepared_by' => auth()->id(),
            'status' => QuotationStatus::Draft,
            'revision_number' => 1,
            ...$totals,
        ]);

        $this->syncCustomFields($quotation, 'quotation', $customData, $companyId);

        $this->syncItems($quotation, $items, $totals);
        QuotationRevisionService::snapshot($quotation);

        return redirect()->route('admin.quotations.show', $quotation)->with('status', __('Quotation created.'));
    }

    public function show(Quotation $quotation): View
    {
        $this->authorize('view', $quotation);

        $quotation->load([
            'customer', 'lead', 'branch', 'items', 'revisions.creator',
            'quotationNotes.user', 'attachments.uploader', 'preparer', 'approver',
            'salesOrder', 'conversion',
        ]);

        return view('admin.sales.quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation): View
    {
        $this->authorize('update', $quotation);

        $quotation->load('items');

        return view('admin.sales.quotations.edit', array_merge(
            ['quotation' => $quotation],
            $this->formMeta($quotation),
        ));
    }

    public function update(Request $request, Quotation $quotation): RedirectResponse
    {
        $this->authorize('update', $quotation);

        QuotationRevisionService::snapshot($quotation);

        $header = $this->validateHeader($request, $quotation);
        [$header, $customData] = $this->partitionCustomFields('quotation', $header, $quotation->company_id, $quotation->branch_id);
        ['items' => $items, 'totals' => $totals] = $this->validatedItems($request);

        $quotation->update($header);
        $this->syncCustomFields($quotation, 'quotation', $customData, $quotation->company_id);
        $this->syncItems($quotation, $items, $totals);

        $quotation->update(['revision_number' => $quotation->revision_number + 1]);
        $quotation->refresh();
        QuotationRevisionService::snapshot($quotation);

        return redirect()->route('admin.quotations.show', $quotation)->with('status', __('Quotation updated.'));
    }

    public function destroy(Quotation $quotation): RedirectResponse
    {
        $this->authorize('delete', $quotation);

        $quotation->delete();

        return redirect()->route('admin.quotations.index')->with('status', __('Quotation deleted.'));
    }

    public function submitForApproval(Quotation $quotation): RedirectResponse
    {
        $this->authorize('transition', $quotation);

        $quotation->transitionTo(QuotationStatus::PendingApproval);
        QuotationRevisionService::snapshot($quotation);

        return back()->with('status', __('Submitted for approval.'));
    }

    public function approve(Quotation $quotation): RedirectResponse
    {
        $this->authorize('approve', $quotation);

        $actor = auth()->user();
        $delegationService = app(ApprovalDelegationService::class);

        if (! $actor->can('quotations.approve')) {
            $delegation = $delegationService->resolveActiveDelegation(
                $actor,
                ApprovalRuleType::QuotationApproval->value,
                DocumentModule::Commercial->value,
                $quotation->company_id,
                $quotation->branch_id,
                'quotations.approve',
            );

            if ($delegation) {
                $delegationService->recordDelegatedApproval(
                    $actor,
                    $quotation,
                    $delegation,
                    'quotation.approved_via_delegation',
                    'commercial',
                );
            }
        }

        $quotation->transitionTo(QuotationStatus::Sent);
        $quotation->update([
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        QuotationRevisionService::snapshot($quotation);
        \App\Support\ActivityLogger::log('quote_approved', $quotation);
        app(WorkflowRulesService::class)->dispatch(WorkflowRuleTrigger::Approved, $quotation, $actor);

        return back()->with('status', __('Quotation approved and sent.'));
    }

    public function send(Quotation $quotation): RedirectResponse
    {
        $this->authorize('send', $quotation);

        if ($quotation->status === QuotationStatus::PendingApproval) {
            $quotation->transitionTo(QuotationStatus::Sent);
        }

        QuotationRevisionService::snapshot($quotation);

        return back()->with('status', __('Quotation marked as sent.'));
    }

    public function markViewed(Quotation $quotation): RedirectResponse
    {
        $this->authorize('transition', $quotation);

        $quotation->transitionTo(QuotationStatus::Viewed);
        QuotationRevisionService::snapshot($quotation);

        return back()->with('status', __('Quotation marked as viewed.'));
    }

    public function accept(Quotation $quotation): RedirectResponse
    {
        $this->authorize('transition', $quotation);

        $quotation->transitionTo(QuotationStatus::Accepted);
        QuotationRevisionService::snapshot($quotation);
        app(WorkflowRulesService::class)->dispatch(WorkflowRuleTrigger::Approved, $quotation, auth()->user());

        return back()->with('status', __('Quotation accepted.'));
    }

    public function reject(Quotation $quotation): RedirectResponse
    {
        $this->authorize('transition', $quotation);

        $quotation->transitionTo(QuotationStatus::Rejected);
        QuotationRevisionService::snapshot($quotation);
        app(WorkflowRulesService::class)->dispatch(WorkflowRuleTrigger::Rejected, $quotation, auth()->user());

        return back()->with('status', __('Quotation rejected.'));
    }

    public function convert(Quotation $quotation): RedirectResponse
    {
        $this->authorize('convert', $quotation);

        try {
            $salesOrder = QuotationConversionService::convert($quotation, (int) auth()->id());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return redirect()
            ->route('admin.sales-orders.show', $salesOrder)
            ->with('status', __('Quotation converted to sales order.'));
    }

    public function expire(Quotation $quotation): RedirectResponse
    {
        $this->authorize('transition', $quotation);

        $quotation->transitionTo(QuotationStatus::Expired);
        QuotationRevisionService::snapshot($quotation);

        return back()->with('status', __('Quotation expired.'));
    }

    protected function validateHeader(Request $request, ?Quotation $quotation = null): array
    {
        $companyId = $quotation?->company_id ?? tenant()->companyId() ?? auth()->user()->company_id;
        $branchId = $quotation?->branch_id ?? tenant()->branchId();

        $rules = $this->formSettings->mergeValidationRules('quotation', [
            'customer_id' => [Rule::exists('customers', 'id')->where('company_id', $companyId)],
            'lead_id' => [Rule::exists('leads', 'id')->where('company_id', $companyId)],
            'quotation_date' => ['date'],
            'valid_until' => ['date', 'after_or_equal:quotation_date'],
            'currency' => ['string', 'size:3'],
            'notes' => ['string'],
            'company_id' => ['sometimes', 'exists:companies,id'],
            'branch_id' => ['sometimes', 'exists:branches,id'],
        ], $companyId, $branchId);

        $data = $request->validate($rules);

        return $this->formSettings->applyDefaults('quotation', $data, $companyId, $branchId);
    }

    protected function nextQuotationNumber(int $companyId, int $branchId): string
    {
        return app(NumberingService::class)->next(
            DocumentType::Quotation,
            $companyId,
            $branchId,
        );
    }

    protected function formMeta(?Quotation $quotation = null): array
    {
        $companyId = $quotation?->company_id ?? tenant()->companyId() ?? auth()->user()->company_id;

        $branchId = $quotation?->branch_id ?? tenant()->branchId();

        return [
            'formFields' => $this->formSettings->resolvedFields('quotation', $companyId, $branchId, $quotation),
            'companies' => auth()->user()->hasRole('Super Admin')
                ? Company::query()->where('is_active', true)->orderBy('name')->get()
                : Company::query()->where('id', auth()->user()->company_id)->get(),
            'branches' => Branch::query()->where('company_id', $companyId)->where('is_active', true)->get(),
            'customers' => Customer::query()->forTenant()->orderBy('company_name')->get(),
            'leads' => Lead::query()->forTenant()->orderBy('lead_name')->get(),
            'itemTypes' => \App\Enums\QuotationItemType::cases(),
        ];
    }
}

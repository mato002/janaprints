<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Enums\DocumentType;
use App\Enums\QuotationStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\HandlesFormCustomFields;
use App\Http\Controllers\Admin\Concerns\HandlesModalFormResponses;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Crm\Concerns\ResolvesCrmTenant;
use App\Http\Controllers\Admin\Sales\Concerns\ManagesQuotationItems;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Sales\Quotation;
use App\Rules\DateNotInThePast;
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
use App\Support\Sales\SalesDeskViews;
use App\Support\Sales\QuotationApprovalService;
use App\Support\Sales\QuotationArtworkLinkService;
use App\Support\Sales\ReturnsToSalesDesk;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuotationController extends Controller
{
    use HandlesFormCustomFields, HandlesModalFormResponses, ManagesQuotationItems, ResolvesCrmTenant, ReturnsToSalesDesk, ScopesToTenant;

    public function __construct(
        protected FormSettingsService $formSettings,
        protected QuotationApprovalService $quotationApprovals,
        protected QuotationArtworkLinkService $quotationArtwork,
    ) {}

    public function index(): RedirectResponse
    {
        $this->authorize('viewAny', Quotation::class);

        return redirect()->to(SalesDeskViews::deskUrl(SalesDeskViews::QUOTES, request()->query()));
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

    public function store(Request $request): RedirectResponse|Response
    {
        $this->authorize('create', Quotation::class);

        $header = $this->validateHeader($request);
        $request->validate([
            'customer_artwork_id' => ['nullable', 'integer'],
        ]);
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

        $this->quotationApprovals->publishOnCreate($quotation->fresh(), (int) auth()->id());
        $this->maybeLinkArtworkOnCreate($request, $quotation->fresh());

        $redirect = redirect()->route('admin.quotations.show', $quotation);

        if ($this->wantsSalesDeskReturn($request) && $quotation->customer_id) {
            $customer = Customer::query()->find($quotation->customer_id);
            $redirect = redirect()->route('admin.sales.desk', [
                'customer' => $customer?->getRouteKey(),
                'step' => 2,
            ]);
        }

        return $this->modalOrRedirect(
            __('Quotation created and published to the client.'),
            $redirect,
        );
    }

    public function show(Quotation $quotation): View
    {
        $this->authorize('view', $quotation);

        $quotation->load([
            'customer', 'lead', 'branch', 'items', 'revisions.creator',
            'quotationNotes.user', 'preparer', 'approver',
            'salesOrder', 'conversion',
        ]);

        $linkedArtworkAnalysis = PrintArtworkAnalysis::query()
            ->where('quotation_id', $quotation->id)
            ->latest('id')
            ->first();

        $appliedQuotationEstimate = PrintQuotationEstimate::query()
            ->where('quotation_id', $quotation->id)
            ->where('estimation_status', 'applied_to_quotation')
            ->latest('applied_at')
            ->with('appliedByUser')
            ->first();

        return view('admin.sales.quotations.show', compact(
            'quotation',
            'linkedArtworkAnalysis',
            'appliedQuotationEstimate',
        ) + [
            'artworkLink' => $this->quotationArtwork->presentForQuotation($quotation),
        ]);
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

    public function update(Request $request, Quotation $quotation): RedirectResponse|Response
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

        return $this->modalOrRedirect(
            __('Quotation updated.'),
            redirect()->route('admin.quotations.show', $quotation),
        );
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

        if ($quotation->status->canTransitionTo(QuotationStatus::Sent)) {
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

    public function linkArtwork(Request $request, Quotation $quotation): RedirectResponse
    {
        $this->authorize('linkArtwork', $quotation);

        $validated = $request->validate([
            'artwork_source' => ['required', 'in:library,request'],
            'customer_artwork_id' => ['required_if:artwork_source,library', 'nullable', 'integer'],
            'artwork_request_id' => ['required_if:artwork_source,request', 'nullable', 'integer'],
        ]);

        $artworkId = $validated['artwork_source'] === 'library'
            ? (int) $validated['customer_artwork_id']
            : (int) $validated['artwork_request_id'];

        $this->quotationArtwork->link(
            $quotation,
            $validated['artwork_source'],
            $artworkId,
            (int) auth()->id(),
        );

        return back()->with('status', __('Artwork linked to this quotation.'));
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

    public function customerArtworks(Customer $customer): JsonResponse
    {
        $this->authorize('create', Quotation::class);

        abort_unless(
            Customer::query()->forTenant()->whereKey($customer->id)->exists(),
            403,
        );

        $artworks = app(CustomerOrderContextService::class)
            ->artworkLibrary($customer)
            ->map(fn ($artwork) => [
                'id' => $artwork->id,
                'label' => $artwork->artwork_name.' · '.$artwork->versionLabel(),
            ])
            ->values();

        return response()->json(['artworks' => $artworks]);
    }

    protected function maybeLinkArtworkOnCreate(Request $request, Quotation $quotation): void
    {
        if (! $request->filled('customer_artwork_id')) {
            return;
        }

        $this->quotationArtwork->linkFromLibrary(
            $quotation,
            (int) $request->input('customer_artwork_id'),
            (int) auth()->id(),
        );
    }

    protected function validateHeader(Request $request, ?Quotation $quotation = null): array
    {
        $companyId = $quotation?->company_id ?? tenant()->companyId() ?? auth()->user()->company_id;
        $branchId = $quotation?->branch_id ?? tenant()->branchId();

        $data = $this->formSettings->validateRequest($request, 'quotation', [
            'customer_id' => [Rule::exists('customers', 'id')->where('company_id', $companyId)],
            'lead_id' => [Rule::exists('leads', 'id')->where('company_id', $companyId)],
            'quotation_date' => ['date'],
            'valid_until' => ['date', 'after_or_equal:quotation_date', new DateNotInThePast($quotation?->valid_until)],
            'currency' => ['string', 'size:3'],
            'notes' => ['string'],
            'company_id' => ['sometimes', 'exists:companies,id'],
            'branch_id' => ['sometimes', 'exists:branches,id'],
        ], $companyId, $branchId, serverProvidedFields: ['company_id', 'branch_id']);

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

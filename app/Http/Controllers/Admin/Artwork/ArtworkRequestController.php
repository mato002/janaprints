<?php

namespace App\Http\Controllers\Admin\Artwork;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkPriority;
use App\Enums\ArtworkRequestStatus;
use App\Enums\DocumentType;
use App\Http\Controllers\Admin\Concerns\HandlesFormCustomFields;
use App\Http\Controllers\Admin\Concerns\HandlesModalFormResponses;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Crm\Concerns\ResolvesCrmTenant;
use App\Http\Controllers\Controller;
use App\Models\Artwork\ArtworkApproval;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Crm\Customer;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Enums\WorkflowRuleTrigger;
use App\Support\Governance\WorkflowRulesService;
use App\Support\Platform\FormSettingsService;
use App\Support\Platform\NumberingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArtworkRequestController extends Controller
{
    use HandlesFormCustomFields, HandlesModalFormResponses, ResolvesCrmTenant, ScopesToTenant;

    public function __construct(
        protected FormSettingsService $formSettings,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', ArtworkRequest::class);

        $requests = $this->scopeToTenant(
            ArtworkRequest::query()->with(['customer', 'branch', 'requester', 'assignedDesigner'])
        )->latest()->paginate(15);

        return view('admin.artwork.requests.index', compact('requests'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', ArtworkRequest::class);

        return view('admin.artwork.requests.create', array_merge($this->formMeta(), [
            'presetCustomerId' => $request->integer('customer_id') ?: null,
        ]));
    }

    public function store(Request $request): RedirectResponse|Response
    {
        $this->authorize('create', ArtworkRequest::class);

        $validated = $this->validateRequest($request);
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);
        [$validated, $customData] = $this->partitionCustomFields('artwork', $validated, $companyId, $branchId);

        $artworkRequest = ArtworkRequest::query()->create([
            ...$validated,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'request_number' => $this->nextRequestNumber($companyId, $branchId),
            'requested_by' => auth()->id(),
            'status' => ArtworkRequestStatus::Requested,
            'current_version' => 0,
        ]);

        $this->syncCustomFields($artworkRequest, 'artwork', $customData, $companyId);

        return $this->modalOrRedirect(
            __('Artwork request created.'),
            redirect()->route('admin.artwork.show', $artworkRequest),
        );
    }

    public function show(ArtworkRequest $artworkRequest): View
    {
        $this->authorize('view', $artworkRequest);

        $artworkRequest->load([
            'customer', 'quotation', 'branch', 'requester', 'assignedDesigner',
            'files.uploader', 'versions.uploader', 'comments.user', 'approvals.approver', 'approvals.artworkVersion',
        ]);

        return view('admin.artwork.requests.show', ['request' => $artworkRequest]);
    }

    public function edit(ArtworkRequest $artworkRequest): View
    {
        $this->authorize('update', $artworkRequest);

        return view('admin.artwork.requests.edit', [
            'request' => $artworkRequest,
            ...$this->formMeta($artworkRequest),
        ]);
    }

    public function update(Request $httpRequest, ArtworkRequest $artworkRequest): RedirectResponse|Response
    {
        $this->authorize('update', $artworkRequest);

        $validated = $this->validateRequest($httpRequest, $artworkRequest);
        [$validated, $customData] = $this->partitionCustomFields('artwork', $validated, $artworkRequest->company_id, $artworkRequest->branch_id);
        $artworkRequest->update($validated);
        $this->syncCustomFields($artworkRequest, 'artwork', $customData, $artworkRequest->company_id);

        return $this->modalOrRedirect(
            __('Artwork request updated.'),
            redirect()->route('admin.artwork.show', $artworkRequest),
        );
    }

    public function destroy(ArtworkRequest $artworkRequest): RedirectResponse
    {
        $this->authorize('delete', $artworkRequest);

        $artworkRequest->delete();

        return redirect()
            ->route('admin.artwork.index')
            ->with('status', __('Artwork request deleted.'));
    }

    public function assign(Request $httpRequest, ArtworkRequest $artworkRequest): RedirectResponse
    {
        $this->authorize('assign', $artworkRequest);

        $validated = $httpRequest->validate([
            'assigned_designer_id' => ['required', 'exists:users,id'],
        ]);

        $artworkRequest->update(['assigned_designer_id' => $validated['assigned_designer_id']]);

        if ($artworkRequest->status === ArtworkRequestStatus::Requested) {
            $artworkRequest->transitionTo(ArtworkRequestStatus::InDesign);
        } elseif ($artworkRequest->status === ArtworkRequestStatus::RevisionRequested) {
            $artworkRequest->transitionTo(ArtworkRequestStatus::InDesign);
        }

        return back()->with('status', __('Designer assigned.'));
    }

    public function submit(ArtworkRequest $artworkRequest): RedirectResponse
    {
        $this->authorize('submit', $artworkRequest);

        $artworkRequest->transitionTo(ArtworkRequestStatus::Submitted);

        return back()->with('status', __('Artwork submitted for approval.'));
    }

    public function startDesign(ArtworkRequest $artworkRequest): RedirectResponse
    {
        $this->authorize('startDesign', $artworkRequest);

        $artworkRequest->transitionTo(ArtworkRequestStatus::InDesign);

        return back()->with('status', __('Artwork returned to design.'));
    }

    public function approve(Request $httpRequest, ArtworkRequest $artworkRequest): RedirectResponse
    {
        $this->authorize('approve', $artworkRequest);

        $validated = $httpRequest->validate([
            'decision' => ['required', Rule::enum(ArtworkApprovalDecision::class)],
            'comments' => ['nullable', 'string', 'max:5000'],
        ]);

        $decision = ArtworkApprovalDecision::from($validated['decision']);
        $version = $artworkRequest->currentVersionRecord();

        if (! $version) {
            return back()->withErrors(['decision' => __('No version available for approval.')]);
        }

        ArtworkApproval::query()->create([
            'company_id' => $artworkRequest->company_id,
            'branch_id' => $artworkRequest->branch_id,
            'artwork_request_id' => $artworkRequest->id,
            'artwork_version_id' => $version->id,
            'approved_by' => auth()->id(),
            'decision' => $decision,
            'comments' => $validated['comments'] ?? null,
        ]);

        $newStatus = match ($decision) {
            ArtworkApprovalDecision::Approved => ArtworkRequestStatus::Approved,
            ArtworkApprovalDecision::Rejected => ArtworkRequestStatus::Rejected,
            ArtworkApprovalDecision::RevisionRequested => ArtworkRequestStatus::RevisionRequested,
        };

        $artworkRequest->transitionTo($newStatus);

        $trigger = match ($decision) {
            ArtworkApprovalDecision::Approved => WorkflowRuleTrigger::Approved,
            ArtworkApprovalDecision::Rejected => WorkflowRuleTrigger::Rejected,
            ArtworkApprovalDecision::RevisionRequested => WorkflowRuleTrigger::Rejected,
        };
        app(WorkflowRulesService::class)->dispatch($trigger, $artworkRequest->fresh(), auth()->user());

        return back()->with('status', __('Approval recorded.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateRequest(Request $request, ?ArtworkRequest $existing = null): array
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        $customerRule = Rule::exists('customers', 'id')
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId);

        $quotationRule = Rule::exists('quotations', 'id')
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId);

        return $request->validate($this->formSettings->mergeValidationRules('artwork', [
            'customer_id' => [$customerRule],
            'quotation_id' => [$quotationRule],
            'title' => ['string', 'max:255'],
            'description' => ['string'],
            'priority' => [Rule::enum(ArtworkPriority::class)],
            'due_date' => ['date'],
            'assigned_designer_id' => ['exists:users,id'],
        ], $companyId, $branchId));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(?ArtworkRequest $artworkRequest = null): array
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds(request());

        return [
            'formFields' => $this->formSettings->resolvedFields('artwork', $companyId, $branchId, $artworkRequest),
            'customers' => Customer::query()
                ->where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->orderBy('company_name')
                ->get(['id', 'company_name', 'customer_code']),
            'quotations' => Quotation::query()
                ->forTenant()
                ->orderByDesc('quotation_date')
                ->limit(50)
                ->get(['id', 'quotation_number']),
            'designers' => User::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'priorities' => ArtworkPriority::cases(),
        ];
    }

    protected function nextRequestNumber(int $companyId, int $branchId): string
    {
        return app(NumberingService::class)->next(
            DocumentType::ArtworkRequest,
            $companyId,
            $branchId,
        );
    }
}

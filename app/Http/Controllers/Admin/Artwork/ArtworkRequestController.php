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
use App\Support\Artwork\DesignerOperatorMode;
use App\Support\Artwork\ReturnsToDesignerDesk;
use App\Support\Sales\ReturnsToSalesDesk;
use App\Support\Sales\SalesDeskViews;
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
    use HandlesFormCustomFields, HandlesModalFormResponses, ResolvesCrmTenant, ReturnsToDesignerDesk, ReturnsToSalesDesk, ScopesToTenant;

    public function __construct(
        protected FormSettingsService $formSettings,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $this->authorize('viewAny', ArtworkRequest::class);

        if (DesignerOperatorMode::enabledFor($request->user())) {
            return redirect()->to(DesignerOperatorMode::homeUrl());
        }

        return redirect()->to(SalesDeskViews::deskUrl(SalesDeskViews::ARTWORK, $request->query()));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', ArtworkRequest::class);

        return view('admin.artwork.requests.create', array_merge($this->formMeta(), [
            'presetCustomerId' => $request->integer('customer_id') ?: null,
            'presetPrintSpecificationId' => $request->integer('customer_print_specification_id') ?: null,
            'fromSalesDesk' => $this->wantsSalesDeskReturn($request),
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

        $customer = $artworkRequest->customer;

        if ($this->wantsSalesDeskReturn($request) && $customer) {
            return $this->modalOrRedirect(
                __('Artwork request created.'),
                redirect()->route('admin.sales.desk', [
                    'customer' => $customer->getRouteKey(),
                    'step' => 2,
                ]),
            );
        }

        return $this->modalOrRedirect(
            __('Artwork request created.'),
            redirect()->route('admin.artwork.show', $artworkRequest),
        );
    }

    public function show(Request $httpRequest, ArtworkRequest $artworkRequest): View
    {
        $this->authorize('view', $artworkRequest);

        $artworkRequest->load([
            'customer', 'quotation', 'branch', 'requester', 'assignedDesigner',
            'files.uploader', 'versions.uploader', 'comments.user', 'approvals.approver', 'approvals.artworkVersion',
        ]);

        if ($this->wantsDesignerDeskReturn($httpRequest)) {
            return view('admin.artwork.desk.request-modal', [
                'request' => $artworkRequest,
                'focusPanel' => $httpRequest->query('panel'),
            ]);
        }

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
            $this->wantsDesignerDeskReturn($httpRequest)
                ? redirect()->to($this->designerDeskUrl())
                : redirect()->route('admin.artwork.show', $artworkRequest),
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

    /**
     * Designer self-claim so open jobs are not worked by two people at once.
     */
    public function claim(Request $request, ArtworkRequest $artworkRequest): RedirectResponse
    {
        // Stale desk UI may re-post Claim after a successful claim that looked like a network error.
        if ((int) $artworkRequest->assigned_designer_id === (int) $request->user()->id) {
            $this->authorize('view', $artworkRequest);

            if ($this->wantsDesignerDeskReturn($request)) {
                return redirect()->to($this->designerDeskUrl(['request' => $artworkRequest->public_id]))
                    ->with('status', __('This job is already on your queue.'));
            }

            return back()->with('status', __('This job is already on your queue.'));
        }

        $this->authorize('claim', $artworkRequest);

        $claimed = false;

        \Illuminate\Support\Facades\DB::transaction(function () use ($artworkRequest, $request, &$claimed) {
            $locked = ArtworkRequest::query()
                ->whereKey($artworkRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->assigned_designer_id !== null) {
                return;
            }

            $locked->update(['assigned_designer_id' => $request->user()->id]);

            if ($locked->status === ArtworkRequestStatus::Requested
                && $locked->status->canTransitionTo(ArtworkRequestStatus::InDesign)) {
                $locked->transitionTo(ArtworkRequestStatus::InDesign);
            } elseif ($locked->status === ArtworkRequestStatus::RevisionRequested
                && $locked->status->canTransitionTo(ArtworkRequestStatus::InDesign)) {
                $locked->transitionTo(ArtworkRequestStatus::InDesign);
            }

            $claimed = true;
        });

        if (! $claimed) {
            return back()->withErrors([
                'workflow' => __('This job was just claimed by another designer.'),
            ]);
        }

        if ($this->wantsDesignerDeskReturn($request)) {
            return redirect()->to($this->designerDeskUrl(['request' => $artworkRequest->fresh()->public_id]))
                ->with('status', __('Job claimed — you are now the working designer. Upload the softcopy PDF when ready, then mark complete.'));
        }

        return back()->with('status', __('Job claimed — you are now the working designer.'));
    }

    public function submit(ArtworkRequest $artworkRequest): RedirectResponse
    {
        $this->authorize('submit', $artworkRequest);

        if ((int) $artworkRequest->assigned_designer_id !== (int) auth()->id()) {
            return back()->withErrors([
                'workflow' => __('Only the assigned designer can mark this job complete.'),
            ]);
        }

        if (! $artworkRequest->canSubmitForApproval()) {
            $message = match ($artworkRequest->status) {
                ArtworkRequestStatus::Requested => __('Claim the job and upload a softcopy PDF before marking complete.'),
                ArtworkRequestStatus::InDesign => __('Upload the softcopy PDF before marking complete.'),
                default => __('This artwork request cannot be submitted in its current status.'),
            };

            return back()->withErrors(['workflow' => $message]);
        }

        $artworkRequest->transitionTo(ArtworkRequestStatus::Submitted);

        if ($this->wantsDesignerDeskReturn()) {
            return redirect()->to($this->designerDeskUrl(['request' => $artworkRequest->public_id]))
                ->with('status', __('Artwork submitted for approval.'));
        }

        return back()->with('status', __('Artwork submitted for approval.'));
    }

    public function startDesign(Request $request, ArtworkRequest $artworkRequest): RedirectResponse
    {
        $this->authorize('startDesign', $artworkRequest);

        if (
            $artworkRequest->status === ArtworkRequestStatus::Submitted
            && $artworkRequest->lacksUploadedVersion()
        ) {
            $artworkRequest->update([
                'status' => ArtworkRequestStatus::InDesign,
                'current_version' => 0,
            ]);

            return $this->designerDeskWorkflowRedirect(
                $request,
                $artworkRequest,
                __('Artwork returned to design so a file can be uploaded.'),
            );
        }

        if (! $artworkRequest->status->canTransitionTo(ArtworkRequestStatus::InDesign)) {
            return back()->withErrors([
                'workflow' => __('This artwork request cannot be moved back to design in its current status.'),
            ]);
        }

        $artworkRequest->transitionTo(ArtworkRequestStatus::InDesign);

        return $this->designerDeskWorkflowRedirect(
            $request,
            $artworkRequest,
            __('Artwork returned to design.'),
        );
    }

    protected function designerDeskWorkflowRedirect(
        Request $request,
        ArtworkRequest $artworkRequest,
        string $message,
    ): RedirectResponse {
        if ($this->wantsDesignerDeskReturn($request)) {
            return redirect()->to($this->designerDeskUrl(['request' => $artworkRequest->public_id]))
                ->with('status', $message);
        }

        return back()->with('status', $message);
    }

    public function approve(Request $httpRequest, ArtworkRequest $artworkRequest): RedirectResponse
    {
        $this->authorize('approve', $artworkRequest);

        if (! $artworkRequest->canReviewSubmission()) {
            return back()->withErrors([
                'workflow' => __('Only submitted artwork awaiting approval can be reviewed.'),
            ]);
        }

        $validated = $httpRequest->validate([
            'decision' => ['required', Rule::enum(ArtworkApprovalDecision::class)],
            'comments' => ['nullable', 'string', 'max:5000'],
        ]);

        $decision = ArtworkApprovalDecision::from($validated['decision']);
        $version = $artworkRequest->currentVersionRecord();

        if (in_array($decision, [ArtworkApprovalDecision::Approved, ArtworkApprovalDecision::RevisionRequested], true)
            && $version === null) {
            return back()->withErrors([
                'decision' => __('Upload artwork before approving or requesting revisions.'),
            ]);
        }

        $newStatus = match ($decision) {
            ArtworkApprovalDecision::Approved => ArtworkRequestStatus::Approved,
            ArtworkApprovalDecision::Rejected => ArtworkRequestStatus::Rejected,
            ArtworkApprovalDecision::RevisionRequested => ArtworkRequestStatus::RevisionRequested,
        };

        if (! $artworkRequest->status->canTransitionTo($newStatus)) {
            return back()->withErrors([
                'decision' => __('This artwork request cannot be moved to the selected status in its current state.'),
            ]);
        }

        ArtworkApproval::query()->create([
            'company_id' => $artworkRequest->company_id,
            'branch_id' => $artworkRequest->branch_id,
            'artwork_request_id' => $artworkRequest->id,
            'artwork_version_id' => $version?->id,
            'approved_by' => auth()->id(),
            'decision' => $decision,
            'comments' => $validated['comments'] ?? null,
        ]);

        $artworkRequest->transitionTo($newStatus);

        $trigger = match ($decision) {
            ArtworkApprovalDecision::Approved => WorkflowRuleTrigger::Approved,
            ArtworkApprovalDecision::Rejected => WorkflowRuleTrigger::Rejected,
            ArtworkApprovalDecision::RevisionRequested => WorkflowRuleTrigger::Rejected,
        };
        app(WorkflowRulesService::class)->dispatch($trigger, $artworkRequest->fresh(), auth()->user());

        $message = match ($decision) {
            ArtworkApprovalDecision::Approved => __('Artwork approved.'),
            ArtworkApprovalDecision::Rejected => __('Artwork request rejected.'),
            ArtworkApprovalDecision::RevisionRequested => __('Revision requested.'),
        };

        return back()->with('status', $message);
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

        return $this->formSettings->validateRequest($request, 'artwork', [
            'customer_id' => [$customerRule],
            'quotation_id' => [$quotationRule],
            'title' => ['string', 'max:255'],
            'description' => ['string'],
            'priority' => [Rule::enum(ArtworkPriority::class)],
            'due_date' => ['date'],
            'assigned_designer_id' => ['exists:users,id'],
        ], $companyId, $branchId);
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

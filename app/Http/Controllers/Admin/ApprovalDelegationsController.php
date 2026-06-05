<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DelegationReason;
use App\Http\Controllers\Admin\Concerns\ResolvesSettingsScope;
use App\Http\Controllers\Controller;
use App\Models\Platform\ApprovalDelegation;
use App\Support\Platform\ApprovalDelegationManager;
use App\Support\Platform\ApprovalDelegationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;

class ApprovalDelegationsController extends Controller
{
    use ResolvesSettingsScope;

    public function __construct(
        protected ApprovalDelegationManager $manager,
        protected ApprovalDelegationService $service,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ApprovalDelegation::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        return view('admin.governance.delegations.index', $this->sharedContext($companyId, $branchId) + [
            'rows' => $this->manager->dashboardRows($companyId, $branchId),
            'canCreate' => auth()->user()->can('create', ApprovalDelegation::class),
            'canManage' => auth()->user()->can('governance.delegations.manage'),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', ApprovalDelegation::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        return view('admin.governance.delegations.create', $this->formContext($companyId, $branchId));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', ApprovalDelegation::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);
        $validated = $this->validatePayload($request);

        try {
            $this->manager->create($companyId, $branchId, $validated, $request->user());
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['delegator_user_id' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.governance.delegations.index', $this->scopeParams($companyId, $branchId))
            ->with('status', __('Approval delegation created.'));
    }

    public function edit(Request $request, ApprovalDelegation $approvalDelegation): View
    {
        $this->authorize('view', $approvalDelegation);
        $this->assertScope($request, $approvalDelegation);

        return view('admin.governance.delegations.edit', $this->formContext(
            $approvalDelegation->company_id,
            $approvalDelegation->branch_id,
        ) + ['delegation' => $approvalDelegation]);
    }

    public function update(Request $request, ApprovalDelegation $approvalDelegation): RedirectResponse
    {
        $this->authorize('update', $approvalDelegation);
        $this->assertScope($request, $approvalDelegation);

        $validated = $this->validatePayload($request);

        try {
            $delegation = $this->manager->update($approvalDelegation, $validated, $request->user());
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['delegator_user_id' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.governance.delegations.index', $this->scopeParams($delegation->company_id, $delegation->branch_id))
            ->with('status', __('Approval delegation updated.'));
    }

    public function cancel(Request $request, ApprovalDelegation $approvalDelegation): RedirectResponse
    {
        $this->authorize('cancel', $approvalDelegation);
        $this->assertScope($request, $approvalDelegation);

        $delegation = $this->manager->cancel($approvalDelegation, $request->user());

        return redirect()
            ->route('admin.governance.delegations.index', $this->scopeParams($delegation->company_id, $delegation->branch_id))
            ->with('status', __('Approval delegation cancelled.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function sharedContext(int $companyId, ?int $branchId): array
    {
        return [
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $this->companiesForSettingsUser(),
            'branches' => $this->branchesForSettingsCompany($companyId),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function formContext(int $companyId, ?int $branchId): array
    {
        return $this->sharedContext($companyId, $branchId) + [
            'users' => $this->manager->usersForCompany($companyId),
            'modules' => $this->service->moduleOptions(),
            'approvalTypes' => $this->service->approvalTypeOptions(),
            'reasons' => collect(DelegationReason::cases())
                ->mapWithKeys(fn (DelegationReason $reason) => [$reason->value => $reason->label()]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatePayload(Request $request): array
    {
        $moduleKeys = array_keys(config('delegation_registry.modules', []));
        $approvalKeys = array_keys(config('approval_registry.rule_types', []));
        $reasonKeys = array_map(fn (DelegationReason $reason) => $reason->value, DelegationReason::cases());

        return $request->validate([
            'delegator_user_id' => ['required', 'integer', 'exists:users,id'],
            'delegate_user_id' => ['required', 'integer', 'exists:users,id', 'different:delegator_user_id'],
            'modules' => ['nullable', 'array'],
            'modules.*' => [Rule::in($moduleKeys)],
            'approval_types' => ['nullable', 'array'],
            'approval_types.*' => [Rule::in($approvalKeys)],
            'reason' => ['required', Rule::in($reasonKeys)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    protected function assertScope(Request $request, ApprovalDelegation $delegation): void
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        abort_unless($delegation->company_id === $companyId, 404);

        if ($branchId !== null && $delegation->branch_id !== null) {
            abort_unless($delegation->branch_id === $branchId, 404);
        }
    }

    /**
     * @return array<string, int|null>
     */
    protected function scopeParams(int $companyId, ?int $branchId): array
    {
        return array_filter([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);
    }
}

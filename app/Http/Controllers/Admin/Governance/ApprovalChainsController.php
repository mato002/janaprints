<?php

namespace App\Http\Controllers\Admin\Governance;

use App\Enums\ApprovalChainMode;
use App\Enums\ApprovalChainStatus;
use App\Enums\ApprovalRuleType;
use App\Governance\ApprovalChainsCenter;
use App\Http\Controllers\Admin\Concerns\ResolvesSettingsScope;
use App\Http\Controllers\Controller;
use App\Models\Governance\ApprovalChain;
use App\Models\User;
use App\Support\Governance\ApprovalChainsManager;
use App\Support\Governance\ApprovalChainsService;
use App\Support\Organization\JobTitleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class ApprovalChainsController extends Controller
{
    use ResolvesSettingsScope;

    public function __construct(
        protected ApprovalChainsManager $manager,
        protected ApprovalChainsService $chains,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ApprovalChainsCenter::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        return view('admin.governance.chains.index', [
            'chains' => $this->manager->chainsForScope($companyId, $branchId),
            'metrics' => $this->chains->summaryMetrics($companyId, $branchId),
            'recentRuns' => $this->chains->recentRuns($companyId, $branchId),
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $this->companiesForSettingsUser(),
            'branches' => $this->branchesForSettingsCompany($companyId),
            'canCreate' => $request->user()->can('create', ApprovalChain::class),
            'canEdit' => $request->user()->can('governance.chains.edit'),
            'canActivate' => $request->user()->can('governance.chains.activate'),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', ApprovalChain::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        return view('admin.governance.chains.form', [
            'chain' => null,
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $this->companiesForSettingsUser(),
            'branches' => $this->branchesForSettingsCompany($companyId),
            ...$this->formOptions($companyId),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', ApprovalChain::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);
        $validated = $this->validateChain($request);

        $this->manager->create($companyId, $branchId, $validated, $validated['steps'] ?? [], $request->user());

        return redirect()
            ->route('admin.governance.chains.index', array_filter([
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ]))
            ->with('success', __('Approval chain created.'));
    }

    public function edit(Request $request, ApprovalChain $chain): View
    {
        $this->authorize('update', $chain);
        $this->assertChainScope($request, $chain);

        return view('admin.governance.chains.form', [
            'chain' => $chain->load('steps.approverUser'),
            'companyId' => $chain->company_id,
            'branchId' => $chain->branch_id,
            'companies' => $this->companiesForSettingsUser(),
            'branches' => $this->branchesForSettingsCompany($chain->company_id),
            ...$this->formOptions($chain->company_id),
        ]);
    }

    public function update(Request $request, ApprovalChain $chain): RedirectResponse
    {
        $this->authorize('update', $chain);
        $this->assertChainScope($request, $chain);

        $validated = $this->validateChain($request);
        $this->manager->update($chain, $validated, $validated['steps'] ?? [], $request->user());

        return redirect()
            ->route('admin.governance.chains.index', array_filter([
                'company_id' => $chain->company_id,
                'branch_id' => $chain->branch_id,
            ]))
            ->with('success', __('Approval chain updated.'));
    }

    public function activate(Request $request, ApprovalChain $chain): RedirectResponse
    {
        $this->authorize('activate', $chain);
        $this->assertChainScope($request, $chain);

        $this->manager->activate($chain, $request->user());

        return back()->with('success', __('Approval chain activated.'));
    }

    public function deactivate(Request $request, ApprovalChain $chain): RedirectResponse
    {
        $this->authorize('activate', $chain);
        $this->assertChainScope($request, $chain);

        $this->manager->deactivate($chain, $request->user());

        return back()->with('success', __('Approval chain deactivated.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateChain(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'module' => ['required', 'string', Rule::in(array_keys(config('chain_registry.modules', [])))],
            'document_type' => ['nullable', 'string', Rule::in(array_keys(config('chain_registry.document_types', [])))],
            'approval_rule_type' => ['required', Rule::enum(ApprovalRuleType::class)],
            'approval_mode' => ['required', Rule::enum(ApprovalChainMode::class)],
            'description' => ['nullable', 'string', 'max:1000'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'min:0'],
            'min_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'max_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.approver_role' => ['nullable', 'string', 'max:255'],
            'steps.*.approver_user_id' => ['nullable', 'exists:users,id'],
            'steps.*.approval_limit' => ['nullable', 'numeric', 'min:0'],
            'steps.*.is_required' => ['nullable', 'boolean'],
            'steps.*.min_amount' => ['nullable', 'numeric', 'min:0'],
            'steps.*.max_amount' => ['nullable', 'numeric', 'min:0'],
            'steps.*.min_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'steps.*.max_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function formOptions(int $companyId): array
    {
        return [
            'modules' => $this->manager->moduleOptions(),
            'ruleTypes' => $this->manager->ruleTypeOptions(),
            'documentTypes' => collect(config('chain_registry.document_types', []))
                ->mapWithKeys(fn (array $meta, string $key) => [$key => __($meta['label'])])
                ->all(),
            'modes' => collect(ApprovalChainMode::cases())
                ->mapWithKeys(fn (ApprovalChainMode $mode) => [$mode->value => $mode->label()])
                ->all(),
            'roles' => Role::query()->where('guard_name', 'web')->orderBy('name')->pluck('name'),
            'users' => User::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'jobTitleAuthorities' => app(JobTitleService::class)->approvalAuthorityOptions($companyId),
        ];
    }

    protected function assertChainScope(Request $request, ApprovalChain $chain): void
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        if ((int) $chain->company_id !== $companyId || $chain->branch_id !== $branchId) {
            abort(404);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin\Governance;

use App\Enums\WorkflowRuleActionType;
use App\Enums\WorkflowRuleTrigger;
use App\Governance\WorkflowRulesCenter;
use App\Http\Controllers\Admin\Concerns\ResolvesSettingsScope;
use App\Http\Controllers\Controller;
use App\Models\Governance\WorkflowRule;
use App\Models\User;
use App\Support\Governance\WorkflowRulesManager;
use App\Support\Governance\WorkflowRulesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class WorkflowRulesController extends Controller
{
    use ResolvesSettingsScope;

    public function __construct(
        protected WorkflowRulesManager $manager,
        protected WorkflowRulesService $rules,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', WorkflowRulesCenter::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        return view('admin.governance.workflow-rules.index', [
            'rules' => $this->manager->rulesForScope($companyId, $branchId),
            'metrics' => $this->manager->summaryMetrics($companyId, $branchId),
            'recentExecutions' => $this->rules->recentExecutions($companyId, $branchId),
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $this->companiesForSettingsUser(),
            'branches' => $this->branchesForSettingsCompany($companyId),
            'canCreate' => $request->user()->can('create', WorkflowRule::class),
            'canManage' => $request->user()->can('governance.workflow.manage'),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', WorkflowRule::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        return view('admin.governance.workflow-rules.form', [
            'rule' => null,
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $this->companiesForSettingsUser(),
            'branches' => $this->branchesForSettingsCompany($companyId),
            ...$this->formOptions($companyId),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', WorkflowRule::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);
        $validated = $this->validateRule($request);

        $this->manager->create($companyId, $branchId, $validated, $validated['actions'] ?? [], $request->user());

        return redirect()
            ->route('admin.governance.workflow-rules.index', array_filter([
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ]))
            ->with('success', __('Workflow rule created.'));
    }

    public function edit(Request $request, WorkflowRule $workflowRule): View
    {
        $this->authorize('update', $workflowRule);
        $this->assertRuleScope($request, $workflowRule);

        return view('admin.governance.workflow-rules.form', [
            'rule' => $workflowRule->load('actions'),
            'companyId' => $workflowRule->company_id,
            'branchId' => $workflowRule->branch_id,
            'companies' => $this->companiesForSettingsUser(),
            'branches' => $this->branchesForSettingsCompany($workflowRule->company_id),
            ...$this->formOptions($workflowRule->company_id),
        ]);
    }

    public function update(Request $request, WorkflowRule $workflowRule): RedirectResponse
    {
        $this->authorize('update', $workflowRule);
        $this->assertRuleScope($request, $workflowRule);

        $validated = $this->validateRule($request);
        $this->manager->update($workflowRule, $validated, $validated['actions'] ?? [], $request->user());

        return redirect()
            ->route('admin.governance.workflow-rules.index', array_filter([
                'company_id' => $workflowRule->company_id,
                'branch_id' => $workflowRule->branch_id,
            ]))
            ->with('success', __('Workflow rule updated.'));
    }

    public function activate(Request $request, WorkflowRule $workflowRule): RedirectResponse
    {
        $this->authorize('activate', $workflowRule);
        $this->assertRuleScope($request, $workflowRule);

        $this->manager->activate($workflowRule, $request->user());

        return back()->with('success', __('Workflow rule activated.'));
    }

    public function deactivate(Request $request, WorkflowRule $workflowRule): RedirectResponse
    {
        $this->authorize('activate', $workflowRule);
        $this->assertRuleScope($request, $workflowRule);

        $this->manager->deactivate($workflowRule, $request->user());

        return back()->with('success', __('Workflow rule deactivated.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateRule(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'entity_type' => ['required', 'string', Rule::in(array_keys(config('workflow_rule_registry.entities', [])))],
            'trigger' => ['required', Rule::enum(WorkflowRuleTrigger::class)],
            'conditions' => ['nullable', 'array'],
            'conditions.*.field' => ['nullable', 'string', 'max:80'],
            'conditions.*.operator' => ['nullable', 'string', Rule::in(array_keys(config('workflow_rule_registry.condition_operators', [])))],
            'conditions.*.value' => ['nullable'],
            'actions' => ['required', 'array', 'min:1'],
            'actions.*.action_type' => ['required', Rule::enum(WorkflowRuleActionType::class)],
            'actions.*.config' => ['nullable', 'array'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function formOptions(int $companyId): array
    {
        return [
            'entities' => $this->manager->entityOptions(),
            'triggers' => $this->manager->triggerOptions(),
            'actionTypes' => $this->manager->actionTypeOptions(),
            'operators' => collect(config('workflow_rule_registry.condition_operators', []))
                ->mapWithKeys(fn (array $meta, string $key) => [$key => __($meta['label'])])
                ->all(),
            'roles' => Role::query()->where('guard_name', 'web')->orderBy('name')->pluck('name'),
            'users' => User::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'notificationTypes' => collect(\App\Enums\NotificationType::cases())
                ->mapWithKeys(fn ($type) => [$type->value => $type->label()])
                ->all(),
        ];
    }

    protected function assertRuleScope(Request $request, WorkflowRule $rule): void
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        if ((int) $rule->company_id !== $companyId || $rule->branch_id !== $branchId) {
            abort(404);
        }
    }
}

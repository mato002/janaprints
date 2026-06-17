<?php

namespace App\Http\Controllers\Admin\Governance;

use App\Enums\EscalationMethod;
use App\Governance\EscalationsCenter;
use App\Http\Controllers\Admin\Concerns\PreservesWorkspaceEmbed;
use App\Http\Controllers\Admin\Concerns\ResolvesSettingsScope;
use App\Http\Controllers\Controller;
use App\Models\Governance\WorkflowEscalationRule;
use App\Support\Governance\EscalationsManager;
use App\Support\Governance\EscalationsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class EscalationsController extends Controller
{
    use PreservesWorkspaceEmbed;
    use ResolvesSettingsScope;

    public function __construct(
        protected EscalationsManager $manager,
        protected EscalationsService $service,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', EscalationsCenter::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        return view('admin.governance.escalations.index', [
            'rows' => $this->manager->dashboardRows($companyId, $branchId),
            'metrics' => $this->service->summaryMetrics($companyId, $branchId),
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $this->companiesForSettingsUser(),
            'branches' => $this->branchesForSettingsCompany($companyId),
            'canManage' => $request->user()->can('governance.escalations.manage'),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', WorkflowEscalationRule::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        return view('admin.governance.escalations.form', $this->formContext($companyId, $branchId));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', WorkflowEscalationRule::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);
        $validated = $this->validatePayload($request);

        $this->manager->create($companyId, $branchId, $validated, $request->user());

        return redirect()
            ->route('admin.governance.escalations.index', $this->scopeParams($companyId, $branchId, $request))
            ->with('status', __('Escalation rule created.'));
    }

    public function edit(Request $request, WorkflowEscalationRule $escalation): View
    {
        $this->authorize('update', $escalation);
        $this->assertScope($request, $escalation);

        return view('admin.governance.escalations.form', $this->formContext(
            $escalation->company_id,
            $escalation->branch_id,
        ) + [
            'rule' => $escalation,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, WorkflowEscalationRule $escalation): RedirectResponse
    {
        $this->authorize('update', $escalation);
        $this->assertScope($request, $escalation);

        $validated = $this->validatePayload($request);
        $rule = $this->manager->update($escalation, $validated, $request->user());

        return redirect()
            ->route('admin.governance.escalations.index', $this->scopeParams($rule->company_id, $rule->branch_id, $request))
            ->with('status', __('Escalation rule updated.'));
    }

    public function activate(Request $request, WorkflowEscalationRule $escalation): RedirectResponse
    {
        $this->authorize('activate', $escalation);
        $this->assertScope($request, $escalation);

        $rule = $this->manager->activate($escalation, $request->user());

        return redirect()
            ->route('admin.governance.escalations.index', $this->scopeParams($rule->company_id, $rule->branch_id, $request))
            ->with('status', __('Escalation rule activated.'));
    }

    public function deactivate(Request $request, WorkflowEscalationRule $escalation): RedirectResponse
    {
        $this->authorize('deactivate', $escalation);
        $this->assertScope($request, $escalation);

        $rule = $this->manager->deactivate($escalation, $request->user());

        return redirect()
            ->route('admin.governance.escalations.index', $this->scopeParams($rule->company_id, $rule->branch_id, $request))
            ->with('status', __('Escalation rule deactivated.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formContext(int $companyId, ?int $branchId): array
    {
        return [
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $this->companiesForSettingsUser(),
            'branches' => $this->branchesForSettingsCompany($companyId),
            'workflows' => $this->service->workflowOptions(),
            'waitingPeriods' => $this->service->waitingPeriodOptions(),
            'escalationMethods' => collect(EscalationMethod::cases())
                ->mapWithKeys(fn (EscalationMethod $method) => [$method->value => $method->label()]),
            'roles' => Role::query()->where('guard_name', 'web')->orderBy('name')->pluck('name'),
            'rule' => null,
            'isEdit' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatePayload(Request $request): array
    {
        $workflowKeys = array_keys(config('escalation_registry.workflows', []));
        $waitingHours = array_keys(config('escalation_registry.waiting_period_presets', []));
        $methodKeys = array_map(fn (EscalationMethod $method) => $method->value, EscalationMethod::cases());

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'workflow_key' => ['required', Rule::in($workflowKeys)],
            'waiting_hours' => ['required', 'integer', Rule::in($waitingHours)],
            'escalation_target_role' => ['required', 'string', 'max:120'],
            'escalation_method' => ['required', Rule::in($methodKeys)],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    protected function assertScope(Request $request, WorkflowEscalationRule $rule): void
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        abort_unless($rule->company_id === $companyId, 404);

        if ($branchId !== null && $rule->branch_id !== null) {
            abort_unless($rule->branch_id === $branchId, 404);
        }
    }

    /**
     * @return array<string, int|null>
     */
    protected function scopeParams(int $companyId, ?int $branchId, ?Request $request = null): array
    {
        return $this->workspaceEmbedParams([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ], $request);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesSettingsScope;
use App\Http\Controllers\Controller;
use App\Models\Platform\SettingsGovernance;
use App\Support\Platform\ApprovalRulesManager;
use App\Support\Platform\SettingsRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ApprovalSettingsController extends Controller
{
    use ResolvesSettingsScope;

    public function __construct(
        protected ApprovalRulesManager $manager,
        protected SettingsRegistry $registry,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SettingsGovernance::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        $rows = $this->manager->rows($companyId, $branchId);
        $activeRuleKey = $request->query('rule');

        if ($activeRuleKey && ! array_key_exists($activeRuleKey, config('approval_registry.rule_types', []))) {
            abort(404);
        }

        return view('admin.settings.approvals.index', [
            'sections' => $this->registry->sections(),
            'rows' => $rows,
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $this->companiesForSettingsUser(),
            'branches' => $this->branchesForSettingsCompany($companyId),
            'roles' => Role::query()->where('guard_name', 'web')->orderBy('name')->pluck('name'),
            'permissions' => Permission::query()->where('guard_name', 'web')->orderBy('name')->pluck('name'),
            'canManage' => auth()->user()->can('update', new SettingsGovernance()),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize('update', new SettingsGovernance());

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        $validated = $request->validate([
            'rules' => ['required', 'array'],
            'rules.*.is_enabled' => ['nullable', 'boolean'],
            'rules.*.min_approvers' => ['nullable', 'integer', 'min:1', 'max:10'],
            'rules.*.tiers' => ['nullable', 'array'],
            'rules.*.tiers.*.threshold_amount' => ['nullable', 'numeric', 'min:0'],
            'rules.*.tiers.*.threshold_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'rules.*.tiers.*.approver_role' => ['nullable', 'string', 'max:255'],
            'rules.*.tiers.*.approver_permission' => ['nullable', 'string', 'max:255'],
        ]);

        $this->manager->save($companyId, $branchId, $validated['rules']);

        $redirectParams = array_filter([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'rule' => $request->input('return_rule'),
        ]);

        return redirect()
            ->route('admin.settings.approvals.index', $redirectParams)
            ->with('status', __('Approval rules updated.'));
    }
}

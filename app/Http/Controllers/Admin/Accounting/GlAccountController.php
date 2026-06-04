<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Enums\GlAccountStatus;
use App\Enums\NormalBalance;
use App\Http\Controllers\Admin\Accounting\Concerns\ResolvesAccountingTenant;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\GlAccountGroup;
use App\Models\Accounting\GlAccountType;
use App\Models\Branch;
use App\Support\Accounting\ChartOfAccountsExplorerService;
use App\Support\Accounting\ChartOfAccountsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GlAccountController extends Controller
{
    use ResolvesAccountingTenant, ScopesToTenant;

    public function __construct(
        protected ChartOfAccountsService $chartOfAccounts,
        protected ChartOfAccountsExplorerService $explorer,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', GlAccount::class);

        $types = $this->explorer->typeRail();
        $firstTypeId = $types[0]['id'] ?? null;

        $bootstrap = [
            'stats' => $this->explorer->summaryStats(),
            'types' => $types,
            'initialTypeId' => $request->integer('type_id') ?: $firstTypeId,
            'initialGroupId' => $request->integer('group_id') ?: null,
            'routes' => [
                'groups' => route('admin.accounting.accounts.explorer.groups'),
                'accounts' => route('admin.accounting.accounts.explorer.accounts'),
                'search' => route('admin.accounting.accounts.explorer.search'),
                'panel' => route('admin.accounting.accounts.explorer.panel', ['account' => '__ID__']),
                'deactivate' => route('admin.accounting.accounts.deactivate', ['account' => '__ID__']),
                'create' => route('admin.accounting.accounts.create'),
            ],
            'permissions' => [
                'create' => $request->user()->can('create', GlAccount::class),
                'edit' => $request->user()->can('accounting.chart.edit'),
                'delete' => $request->user()->can('accounting.chart.delete'),
                'lock' => $request->user()->can('accounting.chart.lock'),
                'ledger' => $request->user()->can('accounting.journals.view'),
            ],
        ];

        return view('admin.accounting.accounts.index', compact('bootstrap'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', GlAccount::class);

        return view('admin.accounting.accounts.create', $this->formMeta($request));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', GlAccount::class);

        ['companyId' => $companyId] = $this->tenantIds();
        $validated = $this->validateAccount($request, $companyId);

        $type = $this->chartOfAccounts->resolveType((int) $validated['gl_account_type_id']);
        $normalBalance = $validated['normal_balance'] ?? $type->normal_balance->value;

        $account = $this->chartOfAccounts->createAccount([
            ...$validated,
            'company_id' => $companyId,
            'branch_id' => $validated['branch_id'] ?? null,
            'normal_balance' => $normalBalance,
            'is_system' => false,
            'is_postable' => $request->boolean('is_postable', true),
            'status' => GlAccountStatus::Active,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()
            ->route('admin.accounting.accounts.show', $account)
            ->with('status', __('Account created.'));
    }

    public function show(GlAccount $account): View
    {
        $this->authorize('view', $account);

        $account->load(['accountType', 'accountGroup', 'parent', 'branch', 'children.accountType']);

        return view('admin.accounting.accounts.show', compact('account'));
    }

    public function edit(GlAccount $account): View
    {
        $this->authorize('update', $account);

        return view('admin.accounting.accounts.edit', [
            'account' => $account,
            ...$this->formMeta(request(), $account),
        ]);
    }

    public function update(Request $request, GlAccount $account): RedirectResponse
    {
        $this->authorize('update', $account);

        $validated = $this->validateAccount($request, $account->company_id, $account);

        $this->chartOfAccounts->updateAccount($account, $validated);

        return redirect()
            ->route('admin.accounting.accounts.show', $account)
            ->with('status', __('Account updated.'));
    }

    public function destroy(GlAccount $account): RedirectResponse
    {
        $this->authorize('delete', $account);

        $this->chartOfAccounts->deleteAccount($account);

        return redirect()
            ->route('admin.accounting.accounts.index')
            ->with('status', __('Account deleted.'));
    }

    public function lock(GlAccount $account): RedirectResponse
    {
        $this->authorize('lock', $account);

        $this->chartOfAccounts->lockAccount($account);

        return back()->with('status', __('Account locked.'));
    }

    public function unlock(GlAccount $account): RedirectResponse
    {
        $this->authorize('unlock', $account);

        $this->chartOfAccounts->unlockAccount($account);

        return back()->with('status', __('Account unlocked.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(Request $request, ?GlAccount $account = null): array
    {
        ['companyId' => $companyId] = $this->tenantIds();

        $parentId = $request->integer('parent_id') ?: $account?->parent_id;

        return [
            'types' => GlAccountType::query()->orderBy('sort_order')->get(),
            'groups' => GlAccountGroup::query()->forTenant()->orderBy('code')->get(),
            'parentAccounts' => GlAccount::query()
                ->forTenant()
                ->when($account, fn ($q) => $q->where('id', '!=', $account->id))
                ->orderBy('code')
                ->get(),
            'branches' => Branch::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(),
            'statuses' => GlAccountStatus::cases(),
            'normalBalances' => NormalBalance::cases(),
            'selectedParentId' => $parentId,
            'selectedTypeId' => $request->integer('type_id') ?: $account?->gl_account_type_id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateAccount(Request $request, int $companyId, ?GlAccount $account = null): array
    {
        $branchId = $request->input('branch_id');

        return $request->validate([
            'gl_account_type_id' => ['required', 'exists:gl_account_types,id'],
            'gl_account_group_id' => ['nullable', 'exists:gl_account_groups,id'],
            'parent_id' => ['nullable', 'exists:gl_accounts,id'],
            'branch_id' => [
                'nullable',
                'integer',
                Rule::exists('branches', 'id')->where('company_id', $companyId),
            ],
            'code' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9A-Za-z\-\.]+$/',
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'normal_balance' => ['nullable', Rule::enum(NormalBalance::class)],
            'status' => ['nullable', Rule::enum(GlAccountStatus::class)],
            'is_postable' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);
    }
}

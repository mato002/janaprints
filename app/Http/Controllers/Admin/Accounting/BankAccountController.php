<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Admin\Accounting\Concerns\ResolvesAccountingTenant;
use App\Http\Controllers\Controller;
use App\Models\Accounting\BankAccount;
use App\Models\Accounting\GlAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankAccountController extends Controller
{
    use ResolvesAccountingTenant;

    public function index(): View
    {
        abort_unless(auth()->user()?->can('accounting.bank.view'), 403);

        $accounts = BankAccount::query()
            ->forTenant()
            ->with('glAccount')
            ->orderBy('name')
            ->get();

        return view('admin.accounting.bank.accounts-index', compact('accounts'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->can('accounting.bank.manage'), 403);

        $glAccounts = GlAccount::query()
            ->forTenant()
            ->where('is_postable', true)
            ->whereHas('accountType', fn ($q) => $q->where('code', 'asset'))
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        return view('admin.accounting.bank.accounts-create', compact('glAccounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('accounting.bank.manage'), 403);

        ['companyId' => $companyId] = $this->tenantIds();

        $validated = $request->validate([
            'gl_account_id' => ['required', 'integer', 'exists:gl_accounts,id'],
            'name' => ['required', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:64'],
            'currency_code' => ['required', 'string', 'size:3'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $gl = GlAccount::query()->forTenant()->where('id', $validated['gl_account_id'])->firstOrFail();

        BankAccount::query()->create([
            'company_id' => $companyId,
            'gl_account_id' => $gl->id,
            'name' => $validated['name'],
            'account_number' => $validated['account_number'] ?? null,
            'currency_code' => strtoupper($validated['currency_code']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.accounting.bank.accounts.index')
            ->with('status', __('Bank account created.'));
    }
}

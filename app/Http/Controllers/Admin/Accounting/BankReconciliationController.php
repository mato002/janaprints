<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Admin\Accounting\Concerns\ResolvesAccountingTenant;
use App\Http\Controllers\Controller;
use App\Models\Accounting\BankAccount;
use App\Models\Accounting\BankStatement;
use App\Models\Accounting\BankStatementLine;
use App\Models\Accounting\JournalLine;
use App\Support\Accounting\BankReconciliationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankReconciliationController extends Controller
{
    use ResolvesAccountingTenant;

    public function __construct(
        protected BankReconciliationService $reconciliation,
    ) {}

    public function index(): View
    {
        abort_unless(auth()->user()?->can('accounting.bank.view'), 403);

        $statements = BankStatement::query()
            ->forTenant()
            ->with(['bankAccount.glAccount', 'creator'])
            ->orderByDesc('statement_date')
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.accounting.bank.reconciliation-index', compact('statements'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->can('accounting.bank.manage'), 403);

        $bankAccounts = BankAccount::query()
            ->forTenant()
            ->where('is_active', true)
            ->with('glAccount')
            ->orderBy('name')
            ->get();

        return view('admin.accounting.bank.reconciliation-create', compact('bankAccounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('accounting.bank.manage'), 403);

        ['companyId' => $companyId] = $this->tenantIds();

        $validated = $request->validate([
            'bank_account_id' => ['required', 'integer', 'exists:bank_accounts,id'],
            'statement_date' => ['required', 'date'],
            'opening_balance' => ['required', 'numeric'],
            'closing_balance' => ['required', 'numeric'],
            'notes' => ['nullable', 'string'],
            'lines' => ['nullable', 'array'],
            'lines.*.line_date' => ['required_with:lines', 'date'],
            'lines.*.description' => ['required_with:lines', 'string', 'max:255'],
            'lines.*.reference' => ['nullable', 'string', 'max:128'],
            'lines.*.amount' => ['required_with:lines', 'numeric'],
        ]);

        BankAccount::query()->forTenant()->where('id', $validated['bank_account_id'])->firstOrFail();

        $statement = $this->reconciliation->createStatement($companyId, (int) auth()->id(), $validated);

        return redirect()
            ->route('admin.accounting.bank.reconciliation.show', $statement)
            ->with('status', __('Bank statement created.'));
    }

    public function show(BankStatement $statement): View
    {
        abort_unless(auth()->user()?->can('accounting.bank.view'), 403);
        abort_unless((int) $statement->company_id === (int) tenant()->companyId(), 404);

        $statement->load(['bankAccount.glAccount', 'lines.matchedJournalLine.journal', 'reconciliation']);
        $suggestions = $this->reconciliation->suggestMatches($statement);
        $glBalance = $this->reconciliation->glBalanceAsOf(
            (int) $statement->company_id,
            (int) $statement->bankAccount->gl_account_id,
            $statement->statement_date->toDateString(),
        );

        return view('admin.accounting.bank.reconciliation-show', compact('statement', 'suggestions', 'glBalance'));
    }

    public function match(Request $request, BankStatement $statement): RedirectResponse
    {
        abort_unless(auth()->user()?->can('accounting.bank.manage'), 403);
        abort_unless((int) $statement->company_id === (int) tenant()->companyId(), 404);

        $validated = $request->validate([
            'statement_line_id' => ['required', 'integer'],
            'journal_line_id' => ['required', 'integer'],
        ]);

        $line = BankStatementLine::query()
            ->where('bank_statement_id', $statement->id)
            ->where('id', $validated['statement_line_id'])
            ->firstOrFail();

        $journalLine = JournalLine::query()->findOrFail($validated['journal_line_id']);

        $this->reconciliation->match($line, $journalLine);

        return back()->with('status', __('Line matched.'));
    }

    public function unmatch(BankStatementLine $line): RedirectResponse
    {
        abort_unless(auth()->user()?->can('accounting.bank.manage'), 403);
        abort_unless((int) $line->statement->company_id === (int) tenant()->companyId(), 404);

        $this->reconciliation->unmatch($line);

        return back()->with('status', __('Match cleared.'));
    }

    public function reconcile(BankStatement $statement): RedirectResponse
    {
        abort_unless(auth()->user()?->can('accounting.bank.manage'), 403);
        abort_unless((int) $statement->company_id === (int) tenant()->companyId(), 404);

        $this->reconciliation->markReconciled($statement, (int) auth()->id());

        return back()->with('status', __('Statement reconciled.'));
    }

    public function importLines(Request $request, BankStatement $statement): RedirectResponse
    {
        abort_unless(auth()->user()?->can('accounting.bank.manage'), 403);
        abort_unless((int) $statement->company_id === (int) tenant()->companyId(), 404);

        $validated = $request->validate([
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.line_date' => ['required', 'date'],
            'lines.*.description' => ['required', 'string', 'max:255'],
            'lines.*.reference' => ['nullable', 'string', 'max:128'],
            'lines.*.amount' => ['required', 'numeric'],
        ]);

        $this->reconciliation->importLines($statement, $validated['lines']);

        return back()->with('status', __('Statement lines imported.'));
    }
}

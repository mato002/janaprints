<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Admin\Accounting\Concerns\ResolvesAccountingTenant;
use App\Http\Controllers\Controller;
use App\Models\Accounting\Budget;
use App\Models\Accounting\FiscalYear;
use App\Models\Accounting\GlAccount;
use App\Support\Accounting\BudgetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetController extends Controller
{
    use ResolvesAccountingTenant;

    public function __construct(
        protected BudgetService $budgets,
    ) {}

    public function index(): View
    {
        abort_unless(auth()->user()?->can('accounting.budgets.view'), 403);

        $budgets = Budget::query()
            ->forTenant()
            ->with('fiscalYear')
            ->orderByDesc('from_date')
            ->paginate(25);

        return view('admin.accounting.budgets.index', compact('budgets'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->can('accounting.budgets.manage'), 403);

        $fiscalYears = FiscalYear::query()->forTenant()->orderByDesc('start_date')->get();
        $accounts = GlAccount::query()
            ->forTenant()
            ->where('is_postable', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        return view('admin.accounting.budgets.create', compact('fiscalYears', 'accounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('accounting.budgets.manage'), 403);

        ['companyId' => $companyId] = $this->tenantIds();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'fiscal_year_id' => ['nullable', 'integer', 'exists:fiscal_years,id'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'lines' => ['nullable', 'array'],
            'lines.*.gl_account_id' => ['required_with:lines', 'integer'],
            'lines.*.period_month' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'lines.*.amount' => ['required_with:lines', 'numeric'],
        ]);

        $budget = $this->budgets->create($companyId, (int) auth()->id(), $validated);

        return redirect()
            ->route('admin.accounting.budgets.show', $budget)
            ->with('status', __('Budget created.'));
    }

    public function show(Budget $budget): View
    {
        abort_unless(auth()->user()?->can('accounting.budgets.view'), 403);
        abort_unless((int) $budget->company_id === (int) tenant()->companyId(), 404);

        $budget->load(['lines.glAccount', 'fiscalYear', 'creator']);

        return view('admin.accounting.budgets.show', compact('budget'));
    }

    public function activate(Budget $budget): RedirectResponse
    {
        abort_unless(auth()->user()?->can('accounting.budgets.manage'), 403);
        abort_unless((int) $budget->company_id === (int) tenant()->companyId(), 404);

        $this->budgets->activate($budget);

        return back()->with('status', __('Budget activated.'));
    }

    public function vsActual(Budget $budget): View
    {
        abort_unless(auth()->user()?->can('accounting.budgets.view'), 403);
        abort_unless((int) $budget->company_id === (int) tenant()->companyId(), 404);

        $report = $this->budgets->vsActual($budget);

        return view('admin.accounting.budgets.vs-actual', [
            'budget' => $budget,
            'report' => $report,
        ]);
    }
}

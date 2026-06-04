<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Admin\Accounting\Concerns\ResolvesAccountingTenant;
use App\Http\Controllers\Controller;
use App\Models\Accounting\FiscalYear;
use App\Support\Accounting\Close\YearEndCloseService;
use App\Support\Accounting\FiscalYearService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FiscalYearController extends Controller
{
    use ResolvesAccountingTenant;

    public function __construct(
        protected FiscalYearService $fiscalYears,
        protected YearEndCloseService $yearEndClose,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', FiscalYear::class);

        $fiscalYears = FiscalYear::query()
            ->forTenant()
            ->with(['periods', 'closedByUser', 'lockedByUser'])
            ->orderByDesc('start_date')
            ->get();

        $currentPeriod = \App\Models\Accounting\AccountingPeriod::query()
            ->forTenant()
            ->where('is_current', true)
            ->with('fiscalYear')
            ->first();

        $startMonth = tenant()->companyId()
            ? $this->fiscalYears->fiscalYearStartMonth((int) tenant()->companyId())
            : 1;

        return view('admin.accounting.periods.index', compact('fiscalYears', 'currentPeriod', 'startMonth'));
    }

    public function create(): View
    {
        $this->authorize('create', FiscalYear::class);

        ['companyId' => $companyId] = $this->tenantIds();
        $startMonth = $this->fiscalYears->fiscalYearStartMonth($companyId);

        return view('admin.accounting.periods.create', [
            'startMonth' => $startMonth,
            'suggestedYear' => (int) now()->format('Y'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', FiscalYear::class);

        ['companyId' => $companyId] = $this->tenantIds();

        $validated = $request->validate([
            'start_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'notes' => ['nullable', 'string'],
        ]);

        $fiscalYear = $this->fiscalYears->generate(
            $companyId,
            (int) $validated['start_year'],
            (int) auth()->id(),
            $validated['notes'] ?? null,
        );

        return redirect()
            ->route('admin.accounting.periods.fiscal-years.show', $fiscalYear)
            ->with('status', __('Fiscal year created with 12 monthly periods.'));
    }

    public function show(FiscalYear $fiscalYear): View
    {
        $this->authorize('view', $fiscalYear);

        $fiscalYear->load(['periods.closedByUser', 'periods.lockedByUser', 'yearEndPrepByUser', 'closedByUser', 'lockedByUser']);

        $closeAudits = \App\Models\Accounting\AccountingCloseAudit::query()
            ->where('fiscal_year_id', $fiscalYear->id)
            ->with(['accountingPeriod', 'journal', 'performedByUser'])
            ->orderByDesc('performed_at')
            ->limit(50)
            ->get();

        return view('admin.accounting.periods.show', compact('fiscalYear', 'closeAudits'));
    }

    public function yearEndPrep(FiscalYear $fiscalYear): RedirectResponse
    {
        $this->authorize('yearEndPrep', $fiscalYear);

        $this->fiscalYears->beginYearEndPreparation($fiscalYear, (int) auth()->id());

        return back()->with('status', __('Fiscal year marked for year-end close preparation.'));
    }

    public function close(FiscalYear $fiscalYear): RedirectResponse
    {
        $this->authorize('close', $fiscalYear);

        $result = $this->yearEndClose->closeFiscalYear($fiscalYear, (int) auth()->id());

        $message = $result['journal']
            ? __('Fiscal year closed. Current Year Earnings transferred to Retained Earnings.')
            : __('Fiscal year closed.');

        return back()->with('status', $message);
    }

    public function lock(FiscalYear $fiscalYear): RedirectResponse
    {
        $this->authorize('lock', $fiscalYear);

        $this->fiscalYears->lockFiscalYear($fiscalYear, (int) auth()->id());

        return back()->with('status', __('Fiscal year locked.'));
    }

    public function reopen(FiscalYear $fiscalYear): RedirectResponse
    {
        $this->authorize('reopen', $fiscalYear);

        $this->yearEndClose->reopenFiscalYear($fiscalYear, (int) auth()->id());

        return back()->with('status', __('Fiscal year reopened and year-end entries reversed.'));
    }
}

<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\Journal;
use App\Support\Accounting\Reports\BalanceSheetReportService;
use App\Support\Accounting\Reports\GeneralLedgerReportService;
use App\Support\Accounting\Reports\ProfitAndLossReportService;
use App\Support\Accounting\Close\FinancialIntegrityService;
use App\Support\Accounting\TrialBalanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialReportController extends Controller
{
    public function __construct(
        protected TrialBalanceService $trialBalance,
        protected BalanceSheetReportService $balanceSheet,
        protected ProfitAndLossReportService $profitAndLoss,
        protected GeneralLedgerReportService $generalLedger,
        protected FinancialIntegrityService $integrity,
    ) {}

    public function trialBalance(Request $request): View
    {
        $this->authorize('viewReports', Journal::class);

        $filters = $this->reportFilters($request);
        $full = $request->boolean('include_zero', true);
        $report = $full
            ? $this->trialBalance->buildFull(array_filter($filters))
            : $this->trialBalance->build(array_filter($filters));

        return view('admin.accounting.reports.trial-balance', [
            'report' => $report,
            'periods' => $this->periods(),
            'filters' => $filters,
            'full' => $full,
        ]);
    }

    public function balanceSheet(Request $request): View
    {
        $this->authorize('viewReports', Journal::class);

        $filters = [
            'as_of_date' => $request->input('as_of_date', now()->toDateString()),
            'period_id' => $request->integer('period_id') ?: null,
        ];

        $report = $this->balanceSheet->build(array_filter($filters));

        return view('admin.accounting.reports.balance-sheet', [
            'report' => $report,
            'periods' => $this->periods(),
            'filters' => $filters,
        ]);
    }

    public function profitAndLoss(Request $request): View
    {
        $this->authorize('viewReports', Journal::class);

        $period = $this->defaultPeriod();
        $filters = [
            'from_date' => $request->input('from_date', $period?->start_date?->toDateString() ?? now()->startOfMonth()->toDateString()),
            'to_date' => $request->input('to_date', $period?->end_date?->toDateString() ?? now()->toDateString()),
            'period_id' => $request->integer('period_id') ?: $period?->id,
        ];

        $report = $this->profitAndLoss->build(array_filter($filters));

        return view('admin.accounting.reports.profit-and-loss', [
            'report' => $report,
            'periods' => $this->periods(),
            'filters' => $filters,
        ]);
    }

    public function financialIntegrity(Request $request): View
    {
        $this->authorize('viewReports', Journal::class);

        $companyId = tenant()->companyId();
        $asOf = $request->input('as_of_date', now()->toDateString());
        $periodId = $request->integer('period_id') ?: null;

        $report = $companyId
            ? $this->integrity->buildIntegrityReport($companyId, $asOf, $periodId)
            : null;

        return view('admin.accounting.reports.financial-integrity', [
            'report' => $report,
            'periods' => $this->periods(),
            'filters' => [
                'as_of_date' => $asOf,
                'period_id' => $periodId,
            ],
        ]);
    }

    public function generalLedger(Request $request): View
    {
        $this->authorize('viewReports', Journal::class);

        $filters = $this->reportFilters($request);
        $filters['account_id'] = $request->integer('account_id') ?: null;

        $report = null;
        $summary = null;

        if ($filters['account_id']) {
            $report = $this->generalLedger->build(array_filter($filters));
        } elseif ($request->boolean('run')) {
            $summary = $this->generalLedger->buildSummary(array_filter($filters));
        }

        return view('admin.accounting.reports.general-ledger', [
            'report' => $report,
            'summary' => $summary,
            'periods' => $this->periods(),
            'accounts' => GlAccount::query()->forTenant()->where('is_postable', true)->orderBy('code')->get(['id', 'code', 'name']),
            'filters' => $filters,
        ]);
    }

    /**
     * @return array{period_id?: int, from_date?: string, to_date?: string}
     */
    protected function reportFilters(Request $request): array
    {
        return [
            'period_id' => $request->integer('period_id') ?: null,
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
        ];
    }

    protected function periods()
    {
        return AccountingPeriod::query()->forTenant()->orderByDesc('start_date')->get();
    }

    protected function defaultPeriod(): ?AccountingPeriod
    {
        return AccountingPeriod::query()->forTenant()->where('is_current', true)->first();
    }
}

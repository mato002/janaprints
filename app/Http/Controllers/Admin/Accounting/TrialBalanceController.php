<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\Journal;
use App\Support\Accounting\TrialBalanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrialBalanceController extends Controller
{
    public function __construct(
        protected TrialBalanceService $trialBalance,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Journal::class);

        $filters = [
            'period_id' => $request->integer('period_id') ?: null,
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
        ];

        $full = $request->boolean('include_zero', true);
        $report = $full
            ? $this->trialBalance->buildFull(array_filter($filters))
            : $this->trialBalance->build(array_filter($filters));

        $periods = AccountingPeriod::query()->forTenant()->orderByDesc('start_date')->get();

        return view('admin.accounting.trial-balance.index', compact('report', 'periods', 'filters', 'full'));
    }
}

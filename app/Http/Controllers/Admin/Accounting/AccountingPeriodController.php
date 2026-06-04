<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountingPeriod;
use App\Support\Accounting\AccountingPeriodService;
use App\Support\Accounting\Close\PeriodCloseService;
use Illuminate\Http\RedirectResponse;

class AccountingPeriodController extends Controller
{
    public function __construct(
        protected AccountingPeriodService $periods,
        protected PeriodCloseService $periodClose,
    ) {}

    public function close(AccountingPeriod $period): RedirectResponse
    {
        $this->authorize('close', $period);

        $result = $this->periodClose->close($period, (int) auth()->id());

        $message = $result['journal']
            ? __('Period :name closed with P&L rollforward to Current Year Earnings.', ['name' => $period->name])
            : __('Period :name closed (no P&L activity to roll forward).', ['name' => $period->name]);

        return back()->with('status', $message);
    }

    public function lock(AccountingPeriod $period): RedirectResponse
    {
        $this->authorize('lock', $period);

        $this->periods->lock($period, (int) auth()->id());

        return back()->with('status', __('Period :name locked.', ['name' => $period->name]));
    }

    public function reopen(AccountingPeriod $period): RedirectResponse
    {
        $this->authorize('reopen', $period);

        $this->periodClose->reopen($period, (int) auth()->id());

        return back()->with('status', __('Period :name reopened and close entries reversed.', ['name' => $period->name]));
    }

    public function unlock(AccountingPeriod $period): RedirectResponse
    {
        $this->authorize('reopen', $period);

        $this->periods->unlock($period);

        return back()->with('status', __('Period :name unlocked.', ['name' => $period->name]));
    }

    public function setCurrent(AccountingPeriod $period): RedirectResponse
    {
        $this->authorize('setCurrent', $period);

        $this->periods->setCurrent($period);

        return back()->with('status', __(':name is now the current period.', ['name' => $period->name]));
    }
}

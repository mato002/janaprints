<?php

namespace App\Support\Accounting;

use App\Enums\AccountingPeriodStatus;
use App\Enums\FiscalYearStatus;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\FiscalYear;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountingPeriodService
{
    public function close(AccountingPeriod $period, int $userId): AccountingPeriod
    {
        $this->assertFiscalYearAllowsPeriodChange($period);

        if ($period->status !== AccountingPeriodStatus::Open) {
            throw ValidationException::withMessages([
                'period' => __('Only open periods can be closed.'),
            ]);
        }

        $period->update([
            'status' => AccountingPeriodStatus::Closed,
            'closed_at' => now(),
            'closed_by' => $userId,
            'is_current' => false,
        ]);

        return $period->fresh(['fiscalYear']);
    }

    public function lock(AccountingPeriod $period, int $userId): AccountingPeriod
    {
        $this->assertFiscalYearAllowsPeriodChange($period);

        if ($period->status !== AccountingPeriodStatus::Closed) {
            throw ValidationException::withMessages([
                'period' => __('Only closed periods can be locked.'),
            ]);
        }

        $period->update([
            'status' => AccountingPeriodStatus::Locked,
            'locked_at' => now(),
            'locked_by' => $userId,
        ]);

        return $period->fresh(['fiscalYear']);
    }

    public function reopen(AccountingPeriod $period): AccountingPeriod
    {
        $this->assertFiscalYearAllowsPeriodChange($period);

        if ($period->status === AccountingPeriodStatus::Locked) {
            throw ValidationException::withMessages([
                'period' => __('Locked periods must be unlocked before reopening.'),
            ]);
        }

        if ($period->status !== AccountingPeriodStatus::Closed) {
            throw ValidationException::withMessages([
                'period' => __('Only closed periods can be reopened.'),
            ]);
        }

        $period->update([
            'status' => AccountingPeriodStatus::Open,
            'closed_at' => null,
            'closed_by' => null,
        ]);

        return $period->fresh(['fiscalYear']);
    }

    public function unlock(AccountingPeriod $period): AccountingPeriod
    {
        $this->assertFiscalYearAllowsPeriodChange($period);

        if ($period->status !== AccountingPeriodStatus::Locked) {
            throw ValidationException::withMessages([
                'period' => __('Only locked periods can be unlocked.'),
            ]);
        }

        $period->update([
            'status' => AccountingPeriodStatus::Closed,
            'locked_at' => null,
            'locked_by' => null,
        ]);

        return $period->fresh(['fiscalYear']);
    }

    public function setCurrent(AccountingPeriod $period): AccountingPeriod
    {
        $this->assertFiscalYearAllowsPeriodChange($period);

        if ($period->status !== AccountingPeriodStatus::Open) {
            throw ValidationException::withMessages([
                'period' => __('Only open periods can be set as the current period.'),
            ]);
        }

        return DB::transaction(function () use ($period) {
            AccountingPeriod::query()
                ->where('company_id', $period->company_id)
                ->update(['is_current' => false]);

            FiscalYear::query()
                ->where('company_id', $period->company_id)
                ->update(['is_current' => false]);

            $period->fiscalYear->update(['is_current' => true]);
            $period->update(['is_current' => true]);

            return $period->fresh(['fiscalYear']);
        });
    }

    protected function assertFiscalYearAllowsPeriodChange(AccountingPeriod $period): void
    {
        $fiscalYear = $period->fiscalYear;

        if ($fiscalYear->status === FiscalYearStatus::Locked) {
            throw ValidationException::withMessages([
                'period' => __('Periods in a locked fiscal year cannot be changed.'),
            ]);
        }

        if ($fiscalYear->status === FiscalYearStatus::Closed) {
            throw ValidationException::withMessages([
                'period' => __('Periods in a closed fiscal year cannot be changed.'),
            ]);
        }
    }
}

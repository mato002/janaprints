<?php

namespace App\Services\Assets;

use App\Enums\AccountingPeriodStatus;
use App\Enums\DepreciationRunStatus;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Assets\DepreciationRun;
use Illuminate\Validation\ValidationException;

class AssetPeriodControlService
{
    public function assertPeriodOpenForPosting(int $companyId, string $journalDate): AccountingPeriod
    {
        $period = AccountingPeriod::query()
            ->where('company_id', $companyId)
            ->whereDate('start_date', '<=', $journalDate)
            ->whereDate('end_date', '>=', $journalDate)
            ->first();

        if (! $period) {
            throw ValidationException::withMessages([
                'period' => __('No accounting period covers :date.', ['date' => $journalDate]),
            ]);
        }

        if ($period->status !== AccountingPeriodStatus::Open) {
            throw ValidationException::withMessages([
                'period' => __('Cannot post depreciation into a closed or locked period.'),
            ]);
        }

        return $period;
    }

    public function assertRunAllowed(int $companyId, string $period): void
    {
        $existing = DepreciationRun::query()
            ->where('company_id', $companyId)
            ->where('period', $period)
            ->first();

        if ($existing && in_array($existing->status, [
            DepreciationRunStatus::Completed,
            DepreciationRunStatus::Running,
        ], true)) {
            throw ValidationException::withMessages([
                'period' => __('A depreciation run already exists for :period.', ['period' => $period]),
            ]);
        }
    }

    public function periodBounds(string $period): array
    {
        $start = "{$period}-01";
        $end = date('Y-m-t', strtotime($start));

        return ['start_date' => $start, 'end_date' => $end];
    }
}

<?php

namespace App\Support\Accounting;

use App\Enums\AccountingPeriodStatus;
use App\Enums\FiscalYearStatus;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\FiscalYear;
use App\Support\Platform\SystemSettingsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FiscalYearService
{
    public function __construct(
        protected SystemSettingsService $settings,
    ) {}

    public function fiscalYearStartMonth(int $companyId): int
    {
        $month = (int) $this->settings->get('fiscal_year_start_month', 1, $companyId, null);

        return max(1, min(12, $month));
    }

    public function generate(int $companyId, int $startYear, int $userId, ?string $notes = null): FiscalYear
    {
        $startMonth = $this->fiscalYearStartMonth($companyId);
        $fyStart = Carbon::create($startYear, $startMonth, 1)->startOfDay();
        $fyEnd = $fyStart->copy()->addYear()->subDay()->endOfDay();

        $code = sprintf('FY%d', $startYear);
        $endYear = (int) $fyEnd->format('Y');
        $name = $startMonth === 1
            ? __('Fiscal Year :year', ['year' => $startYear])
            : __('Fiscal Year :start–:end', ['start' => $startYear, 'end' => $endYear]);

        if (FiscalYear::query()->where('company_id', $companyId)->where('code', $code)->exists()) {
            throw ValidationException::withMessages([
                'start_year' => __('A fiscal year with this code already exists.'),
            ]);
        }

        return DB::transaction(function () use ($companyId, $startYear, $startMonth, $fyStart, $fyEnd, $code, $name, $userId, $notes) {
            $isFirst = ! FiscalYear::query()->where('company_id', $companyId)->exists();

            $fiscalYear = FiscalYear::query()->create([
                'company_id' => $companyId,
                'name' => $name,
                'code' => $code,
                'start_date' => $fyStart->toDateString(),
                'end_date' => $fyEnd->toDateString(),
                'start_month' => $startMonth,
                'status' => FiscalYearStatus::Open,
                'is_current' => $isFirst,
                'notes' => $notes,
            ]);

            $this->createMonthlyPeriods($fiscalYear, $fyStart);

            if ($isFirst) {
                $firstPeriod = $fiscalYear->periods()->orderBy('period_number')->first();
                $firstPeriod?->update(['is_current' => true]);
            }

            return $fiscalYear->load('periods');
        });
    }

    public function beginYearEndPreparation(FiscalYear $fiscalYear, int $userId): FiscalYear
    {
        $this->assertFiscalYearOpenForPrep($fiscalYear);

        $openPeriods = $fiscalYear->periods()->where('status', AccountingPeriodStatus::Open)->count();
        if ($openPeriods > 0) {
            throw ValidationException::withMessages([
                'fiscal_year' => __('Close all monthly periods before year-end preparation.'),
            ]);
        }

        $fiscalYear->update([
            'status' => FiscalYearStatus::YearEndPreparation,
            'year_end_prep_at' => now(),
            'year_end_prep_by' => $userId,
        ]);

        return $fiscalYear->fresh(['periods']);
    }

    public function closeFiscalYear(FiscalYear $fiscalYear, int $userId): FiscalYear
    {
        if (! in_array($fiscalYear->status, [FiscalYearStatus::Open, FiscalYearStatus::YearEndPreparation], true)) {
            throw ValidationException::withMessages([
                'fiscal_year' => __('This fiscal year cannot be closed in its current status.'),
            ]);
        }

        $openOrUnlocked = $fiscalYear->periods()
            ->whereIn('status', [AccountingPeriodStatus::Open])
            ->exists();

        if ($openOrUnlocked) {
            throw ValidationException::withMessages([
                'fiscal_year' => __('All accounting periods must be closed or locked before closing the fiscal year.'),
            ]);
        }

        $fiscalYear->update([
            'status' => FiscalYearStatus::Closed,
            'closed_at' => now(),
            'closed_by' => $userId,
            'is_current' => false,
        ]);

        $fiscalYear->periods()->update(['is_current' => false]);

        return $fiscalYear->fresh(['periods']);
    }

    public function lockFiscalYear(FiscalYear $fiscalYear, int $userId): FiscalYear
    {
        if ($fiscalYear->status !== FiscalYearStatus::Closed) {
            throw ValidationException::withMessages([
                'fiscal_year' => __('Only closed fiscal years can be locked.'),
            ]);
        }

        $fiscalYear->update([
            'status' => FiscalYearStatus::Locked,
            'locked_at' => now(),
            'locked_by' => $userId,
        ]);

        $fiscalYear->periods()
            ->where('status', AccountingPeriodStatus::Closed)
            ->update([
                'status' => AccountingPeriodStatus::Locked,
                'locked_at' => now(),
                'locked_by' => $userId,
            ]);

        return $fiscalYear->fresh(['periods']);
    }

    public function reopenFiscalYear(FiscalYear $fiscalYear): FiscalYear
    {
        if ($fiscalYear->status === FiscalYearStatus::Locked) {
            throw ValidationException::withMessages([
                'fiscal_year' => __('Locked fiscal years cannot be reopened.'),
            ]);
        }

        if ($fiscalYear->status !== FiscalYearStatus::Closed && $fiscalYear->status !== FiscalYearStatus::YearEndPreparation) {
            throw ValidationException::withMessages([
                'fiscal_year' => __('Only closed or year-end preparation fiscal years can be reopened.'),
            ]);
        }

        $fiscalYear->update([
            'status' => FiscalYearStatus::Open,
            'closed_at' => null,
            'closed_by' => null,
            'year_end_prep_at' => null,
            'year_end_prep_by' => null,
        ]);

        return $fiscalYear->fresh(['periods']);
    }

    protected function createMonthlyPeriods(FiscalYear $fiscalYear, Carbon $fyStart): void
    {
        $fyEnd = Carbon::parse($fiscalYear->end_date);

        for ($i = 0; $i < 12; $i++) {
            $periodStart = $i === 0
                ? $fyStart->copy()
                : $fyStart->copy()->addMonths($i)->startOfMonth();

            $periodEnd = $i < 11
                ? $periodStart->copy()->endOfMonth()
                : $fyEnd->copy();

            $periodNumber = $i + 1;
            $code = $periodStart->format('Y-m');

            AccountingPeriod::query()->create([
                'company_id' => $fiscalYear->company_id,
                'fiscal_year_id' => $fiscalYear->id,
                'period_number' => $periodNumber,
                'name' => $periodStart->format('F Y'),
                'code' => $code,
                'start_date' => $periodStart->toDateString(),
                'end_date' => $periodEnd->toDateString(),
                'status' => AccountingPeriodStatus::Open,
                'is_current' => false,
            ]);
        }
    }

    protected function assertFiscalYearOpenForPrep(FiscalYear $fiscalYear): void
    {
        if ($fiscalYear->status === FiscalYearStatus::Locked) {
            throw ValidationException::withMessages([
                'fiscal_year' => __('Locked fiscal years cannot enter year-end preparation.'),
            ]);
        }

        if ($fiscalYear->status === FiscalYearStatus::Closed) {
            throw ValidationException::withMessages([
                'fiscal_year' => __('Closed fiscal years cannot enter year-end preparation.'),
            ]);
        }
    }
}

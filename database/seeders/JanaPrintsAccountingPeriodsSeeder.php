<?php

namespace Database\Seeders;

use App\Models\Accounting\FiscalYear;
use App\Models\Company;
use App\Support\Accounting\FiscalYearService;
use App\Support\Platform\SystemSettingsService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class JanaPrintsAccountingPeriodsSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->where('code', 'JANA')->first();

        if (! $company) {
            $this->command?->warn('Accounting periods skipped: JANA company not found.');

            return;
        }

        if (FiscalYear::query()->where('company_id', $company->id)->exists()) {
            $this->command?->warn('Accounting periods already seeded for JANA.');

            return;
        }

        $settings = app(SystemSettingsService::class);
        $settings->set('fiscal_year_start_month', 1, $company->id, null, 'integer');

        $startMonth = app(FiscalYearService::class)->fiscalYearStartMonth($company->id);
        $now = now();
        $startYear = (int) $now->format('Y');

        if ($startMonth > 1 && (int) $now->format('n') < $startMonth) {
            $startYear--;
        }

        app(FiscalYearService::class)->generate(
            $company->id,
            $startYear,
            1,
            'Jana Prints standard fiscal year (seeded).',
        );
    }
}

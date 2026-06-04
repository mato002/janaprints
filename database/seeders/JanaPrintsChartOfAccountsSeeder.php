<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Support\Accounting\JanaPrintsChartOfAccountsSeedService;
use Illuminate\Database\Seeder;

class JanaPrintsChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->where('code', 'JANA')->first();

        if (! $company) {
            $this->command?->warn('Chart of accounts skipped: JANA company not found.');

            return;
        }

        $report = app(JanaPrintsChartOfAccountsSeedService::class)->seedCompany($company);

        if ($this->command) {
            app(JanaPrintsChartOfAccountsSeedService::class)->printReport(
                $report,
                fn (string $line) => $this->command->line($line),
            );
        }
    }
}

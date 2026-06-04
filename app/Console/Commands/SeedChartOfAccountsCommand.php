<?php

namespace App\Console\Commands;

use App\Support\Accounting\JanaPrintsChartOfAccountsSeedService;
use Database\Seeders\GlAccountTypeSeeder;
use Illuminate\Console\Command;

class SeedChartOfAccountsCommand extends Command
{
    protected $signature = 'accounting:seed-chart-of-accounts
                            {--company=JANA : Company code to seed}
                            {--force : Re-sync accounts even when chart appears complete}
                            {--types : Seed global account types before chart}';

    protected $description = 'Seed the Jana Prints production chart of accounts for a company';

    public function handle(JanaPrintsChartOfAccountsSeedService $service): int
    {
        if ($this->option('types')) {
            $this->call('db:seed', ['--class' => GlAccountTypeSeeder::class, '--no-interaction' => true]);
        }

        $report = $service->seedByCompanyCode(
            (string) $this->option('company'),
            (bool) $this->option('force'),
        );

        if (($report['status'] ?? '') === 'error') {
            $this->error($report['message'] ?? __('Seeding failed.'));

            return self::FAILURE;
        }

        $service->printReport($report, fn (string $message) => $this->line($message));

        if (! ($report['hierarchy']['valid'] ?? true)) {
            $this->warn(__('Hierarchy verification reported issues.'));

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}

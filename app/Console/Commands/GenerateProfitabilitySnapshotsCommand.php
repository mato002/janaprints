<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\PrintingIntelligence\ProfitabilitySnapshotGeneratorService;
use Illuminate\Console\Command;

class GenerateProfitabilitySnapshotsCommand extends Command
{
    protected $signature = 'printing:profitability:generate
                            {--company= : Limit to company ID}
                            {--days=90 : Lookback window for jobs}
                            {--snapshot-type= : job|customer|machine|product|period}
                            {--dry-run : Calculate snapshots without persisting}';

    protected $description = 'Generate production profitability snapshots (PI8)';

    public function handle(ProfitabilitySnapshotGeneratorService $service): int
    {
        if (! config('printing_intelligence.profitability_intelligence_enabled', true)) {
            $this->error(__('Production profitability intelligence is disabled.'));

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $days = max(1, (int) $this->option('days'));
        $snapshotType = $this->option('snapshot-type') ?: null;

        if ($snapshotType !== null && ! in_array($snapshotType, ['job', 'customer', 'machine', 'product', 'period'], true)) {
            $this->error(__('Invalid snapshot type. Use job, customer, machine, product, or period.'));

            return self::FAILURE;
        }

        $companies = $this->option('company')
            ? Company::query()->whereKey((int) $this->option('company'))->get()
            : Company::query()->get();

        if ($companies->isEmpty()) {
            $this->warn(__('No companies matched.'));

            return self::SUCCESS;
        }

        $total = 0;
        $this->info(__('Profitability snapshot generation (PI8)'));
        if ($dryRun) {
            $this->warn(__('Dry run — snapshots will not be persisted.'));
        }

        foreach ($companies as $company) {
            $snapshots = $service->generateForCompany((int) $company->id, $days, $snapshotType, ! $dryRun);
            $total += count($snapshots);

            $this->line(__('Company :code — :count snapshot(s)', [
                'code' => $company->code,
                'count' => count($snapshots),
            ]));
        }

        $this->newLine();
        $this->table([__('Metric'), __('Count')], [
            [__('Snapshots generated'), $total],
            [__('Formula version'), config('printing_intelligence.profitability_formula_version', 'PI8-V1')],
        ]);

        return self::SUCCESS;
    }
}

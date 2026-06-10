<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\PrintingIntelligence\ForecastSnapshotGeneratorService;
use Illuminate\Console\Command;

class GenerateForecastSnapshotsCommand extends Command
{
    protected $signature = 'printing:forecast:generate
                            {--company= : Limit to company ID}
                            {--forecast-type= : revenue|profit|capacity|demand|customer|inventory_risk|machine}
                            {--period= : month|quarter|year}
                            {--dry-run : Generate forecasts without persisting}';

    protected $description = 'Generate executive printing intelligence forecast snapshots (PI9)';

    public function handle(ForecastSnapshotGeneratorService $service): int
    {
        if (! config('printing_intelligence.executive_forecasting_enabled', true)) {
            $this->error(__('Executive forecasting is disabled.'));

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $forecastType = $this->option('forecast-type') ?: null;
        $period = $this->option('period') ?: null;

        if ($period !== null && ! in_array($period, ['month', 'quarter', 'year'], true)) {
            $this->error(__('Invalid period. Use month, quarter, or year.'));

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
        $this->info(__('Executive forecast snapshot generation (PI9)'));
        if ($dryRun) {
            $this->warn(__('Dry run — snapshots will not be persisted.'));
        }

        foreach ($companies as $company) {
            $snapshots = $service->generateForCompany((int) $company->id, $forecastType, $period, ! $dryRun);
            $total += count($snapshots);

            $this->line(__('Company :code — :count snapshot(s)', [
                'code' => $company->code,
                'count' => count($snapshots),
            ]));
        }

        $this->newLine();
        $this->table([__('Metric'), __('Count')], [
            [__('Snapshots generated'), $total],
            [__('Formula version'), config('printing_intelligence.forecast_formula_version', 'PI9-V1')],
        ]);

        return self::SUCCESS;
    }
}

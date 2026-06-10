<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\PrintingIntelligence\CalibrationRecommendationService;
use Illuminate\Console\Command;

class RecommendCalibrationCommand extends Command
{
    protected $signature = 'printing:calibration:recommend
                            {--company= : Limit to company ID}
                            {--days=90 : Lookback window for PI6 comparisons}
                            {--dry-run : Generate recommendations without persisting}';

    protected $description = 'Generate calibration recommendations from PI6 variance analytics (PI7)';

    public function handle(CalibrationRecommendationService $service): int
    {
        if (! config('printing_intelligence.calibration_recommendation_enabled', true)) {
            $this->error(__('Calibration recommendations are disabled.'));

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $days = max(1, (int) $this->option('days'));

        $companies = $this->option('company')
            ? Company::query()->whereKey((int) $this->option('company'))->get()
            : Company::query()->get();

        if ($companies->isEmpty()) {
            $this->warn(__('No companies matched.'));

            return self::SUCCESS;
        }

        $total = 0;
        $this->info(__('Calibration recommendation generation (PI7)'));
        if ($dryRun) {
            $this->warn(__('Dry run — recommendations will not be persisted.'));
        }

        foreach ($companies as $company) {
            $rules = $service->generate((int) $company->id, $days, ! $dryRun);
            $total += count($rules);

            foreach ($rules as $rule) {
                $this->line(__('Company :code — :type (:key) proposed :value', [
                    'code' => $company->code,
                    'type' => $rule->rule_type?->value ?? $rule->rule_type,
                    'key' => $rule->rule_key,
                    'value' => $rule->proposed_value,
                ]));
            }
        }

        $this->newLine();
        $this->table([__('Metric'), __('Count')], [
            [__('Recommendations generated'), $total],
        ]);

        return self::SUCCESS;
    }
}

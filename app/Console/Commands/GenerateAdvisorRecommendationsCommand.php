<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\PrintingIntelligence\Advisor\PrintOperationsAdvisorService;
use Illuminate\Console\Command;

class GenerateAdvisorRecommendationsCommand extends Command
{
    protected $signature = 'printing:advisor:generate
                            {--company= : Limit to company ID}
                            {--branch= : Limit to branch ID}
                            {--type= : quotation|artwork|machine|inventory|customer|profitability|forecast}
                            {--dry-run : Generate recommendations without persisting}';

    protected $description = 'Generate print operations advisor recommendations (PI10)';

    public function handle(PrintOperationsAdvisorService $service): int
    {
        if (! config('printing_intelligence.advisor_enabled', true)) {
            $this->error(__('Print operations advisor is disabled.'));

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $type = $this->option('type') ?: null;
        $branchId = $this->option('branch') ? (int) $this->option('branch') : null;

        if ($type !== null && ! in_array($type, [
            'quotation', 'artwork', 'machine', 'inventory', 'customer', 'profitability', 'forecast',
        ], true)) {
            $this->error(__('Invalid advisor type.'));

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
        $this->info(__('Advisor recommendation generation (PI10)'));
        if ($dryRun) {
            $this->warn(__('Dry run — recommendations will not be persisted.'));
        }

        foreach ($companies as $company) {
            $saved = $service->generate((int) $company->id, $branchId, $type, ! $dryRun);
            $total += count($saved);

            $this->line(__('Company :code — :count recommendation(s)', [
                'code' => $company->code,
                'count' => count($saved),
            ]));
        }

        $this->newLine();
        $this->table([__('Metric'), __('Count')], [
            [__('Recommendations generated'), $total],
            [__('Formula version'), config('printing_intelligence.advisor_formula_version', 'PI10-V1')],
        ]);

        return self::SUCCESS;
    }
}

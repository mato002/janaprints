<?php

namespace App\Console\Commands;

use App\Enums\ProductionEstimationStatus;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Services\PrintingIntelligence\ProductionEstimationService;
use Illuminate\Console\Command;

class ArtworkEstimateProductionCommand extends Command
{
    protected $signature = 'printing:artwork:estimate-production
                            {--analysis= : Specific print_artwork_analysis ID}
                            {--company= : Limit to company ID}
                            {--pending : Only pending production estimates}
                            {--failed : Only failed production estimates}
                            {--quantity=1 : Job quantity multiplier}
                            {--limit=50 : Maximum records to process}
                            {--dry-run : List candidates without mutating}';

    protected $description = 'Estimate machine selection and production cost for artwork analyses (PI4)';

    public function handle(ProductionEstimationService $service): int
    {
        if (! config('printing_intelligence.production_costing_enabled', true)) {
            $this->error(__('Production costing is disabled in configuration.'));

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $limit = max(1, (int) $this->option('limit'));
        $quantity = max(1, (int) $this->option('quantity'));

        $query = PrintArtworkAnalysis::query()->orderBy('id');

        if ($analysisId = $this->option('analysis')) {
            $query->whereKey((int) $analysisId);
        }

        if ($companyId = $this->option('company')) {
            $query->where('company_id', (int) $companyId);
        }

        if ($this->option('pending')) {
            $query->whereDoesntHave('productionEstimate');
        }

        if ($this->option('failed')) {
            $query->whereHas('productionEstimate', fn ($q) => $q->where('estimation_status', ProductionEstimationStatus::Failed->value));
        }

        if (! $this->option('analysis') && ! $this->option('pending') && ! $this->option('failed')) {
            $query->where(function ($builder) {
                $builder->whereDoesntHave('productionEstimate')
                    ->orWhereHas('productionEstimate', fn ($q) => $q->whereIn('estimation_status', [
                        ProductionEstimationStatus::Pending->value,
                        ProductionEstimationStatus::ManualReview->value,
                    ]));
            });
        }

        $records = $query->limit($limit)->get();

        if ($records->isEmpty()) {
            $this->warn(__('No artwork analyses matched the criteria.'));

            return self::SUCCESS;
        }

        $summary = [
            'processed' => 0,
            'completed' => 0,
            'manual_review' => 0,
            'failed' => 0,
        ];

        $this->info(__('Artwork production estimation (PI4)'));
        if ($dryRun) {
            $this->warn(__('Dry run — no records will be mutated.'));
        }

        foreach ($records as $analysis) {
            $summary['processed']++;

            if ($dryRun) {
                $this->line(__('Would estimate production for #:id :file', [
                    'id' => $analysis->id,
                    'file' => $analysis->original_filename,
                ]));

                continue;
            }

            $result = $service->estimate($analysis, null, $quantity);
            $status = $result->estimation_status?->value ?? (string) $result->estimation_status;

            match ($status) {
                ProductionEstimationStatus::Completed->value => $summary['completed']++,
                ProductionEstimationStatus::ManualReview->value => $summary['manual_review']++,
                default => $summary['failed']++,
            };

            $this->line(__('Estimated #:id → :status', ['id' => $analysis->id, 'status' => $status]));
        }

        $this->newLine();
        $this->table(
            [__('Metric'), __('Count')],
            collect($summary)->map(fn ($value, $key) => [__(ucfirst(str_replace('_', ' ', $key))), $value])->values()->all(),
        );

        return self::SUCCESS;
    }
}

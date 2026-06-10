<?php

namespace App\Console\Commands;

use App\Enums\InkEstimationStatus;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Services\PrintingIntelligence\InkEstimationService;
use Illuminate\Console\Command;

class ArtworkEstimateInkCommand extends Command
{
    protected $signature = 'printing:artwork:estimate-ink
                            {--analysis= : Specific print_artwork_analysis ID}
                            {--company= : Limit to company ID}
                            {--pending : Only pending ink estimates}
                            {--failed : Only failed ink estimates}
                            {--limit=50 : Maximum records to process}
                            {--dry-run : List candidates without mutating}';

    protected $description = 'Estimate ink consumption and cost for artwork analyses (PI3)';

    public function handle(InkEstimationService $service): int
    {
        if (! config('printing_intelligence.ink_costing_enabled', true)) {
            $this->error(__('Ink costing is disabled in configuration.'));

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $limit = max(1, (int) $this->option('limit'));

        $query = PrintArtworkAnalysis::query()->orderBy('id');

        if ($analysisId = $this->option('analysis')) {
            $query->whereKey((int) $analysisId);
        }

        if ($companyId = $this->option('company')) {
            $query->where('company_id', (int) $companyId);
        }

        if ($this->option('pending')) {
            $query->whereDoesntHave('inkEstimates', fn ($q) => $q->where('estimation_status', InkEstimationStatus::Completed->value));
        }

        if ($this->option('failed')) {
            $query->whereHas('inkEstimates', fn ($q) => $q->where('estimation_status', InkEstimationStatus::Failed->value));
        }

        if (! $this->option('analysis') && ! $this->option('pending') && ! $this->option('failed')) {
            $query->where(function ($builder) {
                $builder->whereDoesntHave('inkEstimates')
                    ->orWhereHas('inkEstimates', fn ($q) => $q->whereIn('estimation_status', [
                        InkEstimationStatus::Pending->value,
                        InkEstimationStatus::ManualReview->value,
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

        $this->info(__('Artwork ink estimation (PI3)'));
        if ($dryRun) {
            $this->warn(__('Dry run — no records will be mutated.'));
        }

        foreach ($records as $analysis) {
            $summary['processed']++;

            if ($dryRun) {
                $this->line(__('Would estimate ink for #:id :file', [
                    'id' => $analysis->id,
                    'file' => $analysis->original_filename,
                ]));

                continue;
            }

            $result = $service->estimate($analysis);
            $status = $result->estimation_status?->value ?? (string) $result->estimation_status;

            match ($status) {
                InkEstimationStatus::Completed->value => $summary['completed']++,
                InkEstimationStatus::ManualReview->value => $summary['manual_review']++,
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

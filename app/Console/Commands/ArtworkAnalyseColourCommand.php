<?php

namespace App\Console\Commands;

use App\Enums\ColourAnalysisStatus;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Services\PrintingIntelligence\ColourAnalysisService;
use Illuminate\Console\Command;

class ArtworkAnalyseColourCommand extends Command
{
    protected $signature = 'printing:artwork:analyse-colour
                            {--analysis= : Specific print_artwork_analysis ID}
                            {--company= : Limit to company ID}
                            {--pending : Only pending colour analyses}
                            {--failed : Only failed colour analyses}
                            {--limit=50 : Maximum records to process}
                            {--dry-run : List candidates without mutating}';

    protected $description = 'Run colour composition and ink coverage analysis on artwork records (PI2)';

    public function handle(ColourAnalysisService $service): int
    {
        if (! config('printing_intelligence.colour_analysis_enabled', true)) {
            $this->error(__('Colour analysis is disabled in configuration.'));

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
            $query->where('colour_analysis_status', ColourAnalysisStatus::Pending->value);
        }

        if ($this->option('failed')) {
            $query->where('colour_analysis_status', ColourAnalysisStatus::Failed->value);
        }

        if (! $this->option('analysis') && ! $this->option('pending') && ! $this->option('failed')) {
            $query->whereIn('colour_analysis_status', [
                ColourAnalysisStatus::Pending->value,
                ColourAnalysisStatus::ManualReview->value,
            ]);
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
            'unsupported' => 0,
        ];

        $this->info(__('Artwork colour analysis (PI2)'));
        if ($dryRun) {
            $this->warn(__('Dry run — no records will be mutated.'));
        }

        foreach ($records as $analysis) {
            $summary['processed']++;

            if ($dryRun) {
                $this->line(__('Would analyse #:id :file', [
                    'id' => $analysis->id,
                    'file' => $analysis->original_filename,
                ]));

                continue;
            }

            $result = $service->analyze($analysis);
            $status = $result->colour_analysis_status?->value ?? (string) $result->colour_analysis_status;

            match ($status) {
                ColourAnalysisStatus::Completed->value => $summary['completed']++,
                ColourAnalysisStatus::ManualReview->value => $summary['manual_review']++,
                ColourAnalysisStatus::Unsupported->value => $summary['unsupported']++,
                default => $summary['failed']++,
            };

            $this->line(__('Analysed #:id → :status', ['id' => $analysis->id, 'status' => $status]));
        }

        $this->newLine();
        $this->table(
            [__('Metric'), __('Count')],
            collect($summary)->map(fn ($value, $key) => [__(ucfirst(str_replace('_', ' ', $key))), $value])->values()->all(),
        );

        return self::SUCCESS;
    }
}

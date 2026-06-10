<?php

namespace App\Console\Commands;

use App\Enums\EstimateActualComparisonStatus;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\Quotation;
use App\Services\PrintingIntelligence\EstimateActualComparisonService;
use Illuminate\Console\Command;

class CompareEstimateActualsCommand extends Command
{
    protected $signature = 'printing:estimate:compare-actuals
                            {--estimate= : Specific print_quotation_estimate ID}
                            {--quotation= : Specific quotation ID}
                            {--job= : Specific production_job_card ID}
                            {--company= : Limit to company ID}
                            {--pending : Include estimates without completed actuals}
                            {--limit=50 : Maximum records to process}
                            {--dry-run : List candidates without persisting comparisons}';

    protected $description = 'Compare Printing Intelligence estimates against actual production costs (PI6)';

    public function handle(EstimateActualComparisonService $service): int
    {
        if (! config('printing_intelligence.estimate_actual_learning_enabled', true)) {
            $this->error(__('Estimate vs actual learning is disabled in configuration.'));

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $limit = max(1, (int) $this->option('limit'));
        $pending = (bool) $this->option('pending');

        $candidates = $this->resolveCandidates($limit, $pending);

        if ($candidates->isEmpty()) {
            $this->warn(__('No comparison candidates matched the criteria.'));

            return self::SUCCESS;
        }

        $summary = [
            'processed' => 0,
            'completed' => 0,
            'manual_review' => 0,
            'failed' => 0,
            'accuracy_total' => 0.0,
            'accuracy_count' => 0,
        ];

        $this->info(__('Estimate vs actual comparison (PI6)'));
        if ($dryRun) {
            $this->warn(__('Dry run — no comparison records will be persisted.'));
        }

        foreach ($candidates as $candidate) {
            $summary['processed']++;

            if ($dryRun) {
                $this->line(__('Would compare :type #:id', [
                    'type' => $candidate['type'],
                    'id' => $candidate['id'],
                ]));

                continue;
            }

            $comparison = match ($candidate['type']) {
                'estimate' => $service->compareEstimate($candidate['model']),
                'job' => $service->compareJob($candidate['model']),
                'quotation' => $service->compareQuotation($candidate['model']),
                default => null,
            };

            if ($comparison === null) {
                $summary['failed']++;

                continue;
            }

            $status = $comparison->comparison_status?->value ?? (string) $comparison->comparison_status;

            match ($status) {
                EstimateActualComparisonStatus::Completed->value => $summary['completed']++,
                EstimateActualComparisonStatus::ManualReview->value => $summary['manual_review']++,
                default => $summary['failed']++,
            };

            if ($comparison->accuracy_score !== null) {
                $summary['accuracy_total'] += (float) $comparison->accuracy_score;
                $summary['accuracy_count']++;
            }

            $this->line(__('Compared :type #:id → :status (accuracy :score)', [
                'type' => $candidate['type'],
                'id' => $candidate['id'],
                'status' => $status,
                'score' => $comparison->accuracy_score !== null ? number_format((float) $comparison->accuracy_score, 1) : '—',
            ]));
        }

        $averageAccuracy = $summary['accuracy_count'] > 0
            ? round($summary['accuracy_total'] / $summary['accuracy_count'], 2)
            : null;

        $this->newLine();
        $this->table(
            [__('Metric'), __('Count')],
            [
                [__('Processed'), $summary['processed']],
                [__('Completed'), $summary['completed']],
                [__('Manual review'), $summary['manual_review']],
                [__('Failed'), $summary['failed']],
                [__('Average accuracy'), $averageAccuracy ?? '—'],
            ],
        );

        return self::SUCCESS;
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{type: string, id: int, model: mixed}>
     */
    protected function resolveCandidates(int $limit, bool $pending): \Illuminate\Support\Collection
    {
        if ($estimateId = $this->option('estimate')) {
            $estimate = PrintQuotationEstimate::query()->find((int) $estimateId);

            return $estimate ? collect([['type' => 'estimate', 'id' => $estimate->id, 'model' => $estimate]]) : collect();
        }

        if ($jobId = $this->option('job')) {
            $job = ProductionJobCard::query()->find((int) $jobId);

            return $job ? collect([['type' => 'job', 'id' => $job->id, 'model' => $job]]) : collect();
        }

        if ($quotationId = $this->option('quotation')) {
            $quotation = Quotation::query()->find((int) $quotationId);

            return $quotation ? collect([['type' => 'quotation', 'id' => $quotation->id, 'model' => $quotation]]) : collect();
        }

        $estimates = PrintQuotationEstimate::query()
            ->when($this->option('company'), fn ($q) => $q->where('company_id', (int) $this->option('company')))
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn ($estimate) => ['type' => 'estimate', 'id' => $estimate->id, 'model' => $estimate]);

        if ($estimates->isNotEmpty()) {
            return $estimates;
        }

        return ProductionJobCard::query()
            ->when($this->option('company'), fn ($q) => $q->where('company_id', (int) $this->option('company')))
            ->whereHas('materialConsumptions')
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn ($job) => ['type' => 'job', 'id' => $job->id, 'model' => $job]);
    }
}

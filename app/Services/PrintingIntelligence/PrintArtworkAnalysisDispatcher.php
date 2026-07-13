<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\ColourAnalysisStatus;
use App\Enums\InkEstimationStatus;
use App\Enums\ProductionEstimationStatus;
use App\Jobs\PrintingIntelligence\ProcessPrintArtworkAnalysisJob;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PublicQuoteRequest;

class PrintArtworkAnalysisDispatcher
{
    public function __construct(
        protected PrintingIntelligenceEnvironmentService $environment,
        protected QrArtworkAnalysisBridgeService $bridge,
        protected ColourAnalysisService $colourAnalysis,
        protected InkEstimationService $inkEstimation,
        protected ProductionEstimationService $productionEstimation,
        protected QuotationEstimationService $quotationEstimation,
    ) {}

    /**
     * @param  list<string>  $steps
     * @param  array<string, mixed>  $options
     * @return array{queued: bool, summary?: array<string, mixed>|null, warnings?: list<string>, message: string}
     */
    public function dispatchForQuoteRequest(
        PublicQuoteRequest $quoteRequest,
        string $artworkFileId,
        array $steps,
        array $options = [],
    ): array {
        if ($this->environment->shouldQueueAnalysis()) {
            $analysis = $this->bridge->findLinkedAnalysis($quoteRequest, $artworkFileId)
                ?? $this->bridge->ensureAnalysis($quoteRequest, $artworkFileId, $options);

            $this->markProcessing($analysis, $steps);

            ProcessPrintArtworkAnalysisJob::dispatch(
                $analysis->id,
                $steps,
                $options,
                $quoteRequest->id,
                $artworkFileId,
            );

            return [
                'queued' => true,
                'summary' => $this->bridge->buildSummary($analysis->fresh()),
                'warnings' => [__('Analysis queued — results will update when processing completes.')],
                'message' => __('Printing Intelligence analysis queued.'),
            ];
        }

        $result = $this->bridge->run($quoteRequest, $artworkFileId, array_merge($options, [
            'steps' => $steps,
        ]));

        return [
            'queued' => false,
            'summary' => $result['summary'],
            'warnings' => $result['warnings'],
            'message' => __('Printing Intelligence analysis completed.'),
        ];
    }

    /**
     * @param  list<string>  $steps
     * @param  array<string, mixed>  $options
     * @return array{queued: bool, message: string}
     */
    public function dispatchForAnalysis(PrintArtworkAnalysis $analysis, array $steps, array $options = []): array
    {
        if ($this->environment->shouldQueueAnalysis()) {
            $this->markProcessing($analysis, $steps);

            ProcessPrintArtworkAnalysisJob::dispatch(
                $analysis->id,
                $steps,
                $options,
            );

            return [
                'queued' => true,
                'message' => __('Analysis queued — refresh this page shortly for results.'),
            ];
        }

        foreach ($steps as $step) {
            match ($step) {
                'colour' => $this->colourAnalysis->analyze($analysis->fresh()),
                'ink' => $this->inkEstimation->estimate($analysis->fresh()),
                'production' => $this->productionEstimation->estimate(
                    $analysis->fresh(),
                    max(1, (int) ($options['quantity'] ?? 1)),
                ),
                'quotation' => $this->quotationEstimation->estimate($analysis->fresh(), array_merge($options, [
                    'quantity' => (int) ($options['quantity'] ?? 1),
                ])),
                default => null,
            };
        }

        return [
            'queued' => false,
            'message' => __('Analysis completed.'),
        ];
    }

    /**
     * @param  list<string>  $steps
     */
    protected function markProcessing(PrintArtworkAnalysis $analysis, array $steps): void
    {
        $updates = [];

        if (in_array('colour', $steps, true)) {
            $updates['colour_analysis_status'] = ColourAnalysisStatus::Processing;
        }

        if ($updates !== []) {
            $analysis->update($updates);
        }

        if (in_array('ink', $steps, true)) {
            $analysis->inkEstimates()
                ->where('estimation_status', InkEstimationStatus::Processing)
                ->exists();
        }

        if (in_array('production', $steps, true) && $analysis->productionEstimate) {
            $analysis->productionEstimate->update([
                'estimation_status' => ProductionEstimationStatus::Processing,
            ]);
        }
    }
}

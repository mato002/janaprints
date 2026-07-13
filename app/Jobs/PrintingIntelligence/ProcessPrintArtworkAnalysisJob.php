<?php

namespace App\Jobs\PrintingIntelligence;

use App\Jobs\PlatformJob;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PublicQuoteRequest;
use App\Services\PrintingIntelligence\ColourAnalysisService;
use App\Services\PrintingIntelligence\InkEstimationService;
use App\Services\PrintingIntelligence\ProductionEstimationService;
use App\Services\PrintingIntelligence\QrArtworkAnalysisBridgeService;
use App\Services\PrintingIntelligence\QuotationEstimationService;
use Throwable;

class ProcessPrintArtworkAnalysisJob extends PlatformJob
{
    public int $tries = 2;

    public int $timeout = 600;

    /**
     * @param  list<string>  $steps
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        public int $analysisId,
        public array $steps,
        public array $options = [],
        public ?int $quoteRequestId = null,
        public string $artworkFileId = 'primary',
    ) {
        parent::__construct();
        $this->useQueue('default');
    }

    public function handle(
        ColourAnalysisService $colourAnalysis,
        InkEstimationService $inkEstimation,
        ProductionEstimationService $productionEstimation,
        QuotationEstimationService $quotationEstimation,
        QrArtworkAnalysisBridgeService $bridge,
    ): void {
        try {
            if ($this->quoteRequestId !== null) {
                $quoteRequest = PublicQuoteRequest::query()->find($this->quoteRequestId);

                if ($quoteRequest !== null) {
                    $bridge->run($quoteRequest, $this->artworkFileId, array_merge($this->options, [
                        'steps' => $this->steps,
                    ]));

                    return;
                }
            }

            $analysis = PrintArtworkAnalysis::query()->find($this->analysisId);

            if ($analysis === null) {
                return;
            }

            $quantity = max(1, (int) ($this->options['quantity'] ?? 1));

            if (in_array('colour', $this->steps, true)) {
                $colourAnalysis->analyze($analysis->fresh());
            }

            if (in_array('ink', $this->steps, true)) {
                $inkEstimation->estimate($analysis->fresh());
            }

            if (in_array('production', $this->steps, true)) {
                $productionEstimation->estimate($analysis->fresh(), $quantity);
            }

            if (in_array('quotation', $this->steps, true)) {
                $quotationEstimation->estimate($analysis->fresh(), [
                    'quantity' => $quantity,
                    'quotation_id' => $this->options['quotation_id'] ?? $analysis->quotation_id,
                ]);
            }
        } catch (Throwable $exception) {
            report($exception);

            throw $exception;
        }
    }
}

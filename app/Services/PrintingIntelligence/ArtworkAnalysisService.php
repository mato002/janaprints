<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\ArtworkAnalysisStatus;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use Illuminate\Http\UploadedFile;
use Throwable;

class ArtworkAnalysisService
{
    public function __construct(
        protected ArtworkIngestionService $ingestion,
        protected ArtworkMetadataExtractionService $metadataExtraction,
    ) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function analyzeUploadedFile(UploadedFile $file, array $context = []): PrintArtworkAnalysis
    {
        if (! config('printing_intelligence.artwork_analysis_enabled', true)) {
            abort(503, __('Artwork analysis is disabled.'));
        }

        $context['uploaded_by'] = $context['uploaded_by'] ?? auth()->id();

        $analysis = $this->ingestion->ingest($file, $context);

        if ($analysis->analysis_status !== ArtworkAnalysisStatus::Pending) {
            return $analysis->load(['pages', 'quotation', 'productionJobCard', 'uploadedBy']);
        }

        $analysis->update(['analysis_status' => ArtworkAnalysisStatus::Processing]);

        try {
            $analysis = $this->metadataExtraction->extract($analysis);
        } catch (Throwable $exception) {
            $analysis->update([
                'analysis_status' => ArtworkAnalysisStatus::Failed,
                'errors' => [__('Analysis failed: :message', ['message' => $exception->getMessage()])],
                'failed_at' => now(),
                'failure_reason' => $exception->getMessage(),
            ]);
        }

        return $analysis->fresh(['pages', 'quotation', 'productionJobCard', 'uploadedBy']);
    }
}

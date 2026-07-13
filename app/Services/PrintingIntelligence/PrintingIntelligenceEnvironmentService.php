<?php

namespace App\Services\PrintingIntelligence;

class PrintingIntelligenceEnvironmentService
{
    public function __construct(
        protected PdfColourAnalyzer $pdfColourAnalyzer,
    ) {}

    /**
     * @return array{ghostscript_available: bool, ghostscript_binary: string, queue_async: bool, warnings: list<string>}
     */
    public function diagnostics(): array
    {
        $binary = (string) config('printing_intelligence.ghostscript_binary', 'gs');
        $ghostscriptAvailable = config('printing_intelligence.ghostscript_enabled', true)
            && $this->pdfColourAnalyzer->ghostscriptAvailable();

        $warnings = [];

        if (config('printing_intelligence.ghostscript_enabled', true) && ! $ghostscriptAvailable) {
            $warnings[] = __('Ghostscript (:binary) is not available. PDF colour analysis will be limited.', [
                'binary' => $binary,
            ]);
        }

        if (config('queue.default') === 'sync' && config('printing_intelligence.async_analysis_enabled', true)) {
            $warnings[] = __('Queue connection is sync — artwork analysis runs inline and may timeout on large files.');
        }

        return [
            'ghostscript_available' => $ghostscriptAvailable,
            'ghostscript_binary' => $binary,
            'queue_async' => $this->shouldQueueAnalysis(),
            'warnings' => $warnings,
        ];
    }

    public function shouldQueueAnalysis(): bool
    {
        if (! config('printing_intelligence.async_analysis_enabled', true)) {
            return false;
        }

        return config('queue.default') !== 'sync';
    }
}

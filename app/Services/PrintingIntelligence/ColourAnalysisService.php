<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\ColourAnalysisStatus;
use App\Enums\CoverageClass;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintArtworkPage;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ColourAnalysisService
{
    public function __construct(
        protected ImageColourAnalyzer $imageAnalyser,
        protected PdfColourAnalyzer $pdfAnalyser,
    ) {}

    public function analyze(PrintArtworkAnalysis $analysis, bool $dryRun = false): PrintArtworkAnalysis
    {
        if (! config('printing_intelligence.colour_analysis_enabled', true)) {
            abort(503, __('Colour analysis is disabled.'));
        }

        if ($dryRun) {
            return $analysis;
        }

        if (! Storage::disk($analysis->disk)->exists($analysis->file_path)) {
            return $this->markFailed($analysis, __('Artwork file missing from storage.'));
        }

        $analysis->update(['colour_analysis_status' => ColourAnalysisStatus::Processing]);

        try {
            $absolutePath = Storage::disk($analysis->disk)->path($analysis->file_path);
            $extension = strtolower((string) $analysis->file_extension);
            $result = $this->dispatchAnalyser($analysis, $absolutePath, $extension);

            return $this->persistResult($analysis, $result);
        } catch (Throwable $exception) {
            return $this->markFailed($analysis, $exception->getMessage());
        }
    }

    /**
     * @return array{status: string, pages: list<array<string, mixed>>, aggregate: array<string, mixed>, warnings: list<string>, raw: array<string, mixed>}
     */
    protected function dispatchAnalyser(PrintArtworkAnalysis $analysis, string $absolutePath, string $extension): array
    {
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'tif', 'tiff'], true)) {
            return $this->imageAnalyser->analyze(
                $absolutePath,
                $analysis->resolution_dpi !== null ? (float) $analysis->resolution_dpi : null,
                $analysis->has_transparency,
            );
        }

        if ($extension === 'pdf') {
            return $this->pdfAnalyser->analyze($absolutePath);
        }

        return [
            'status' => 'unsupported',
            'pages' => [],
            'aggregate' => [],
            'warnings' => [__('File type not supported for colour analysis.')],
            'raw' => ['extension' => $extension],
        ];
    }

    /**
     * @param  array{status: string, pages: list<array<string, mixed>>, aggregate: array<string, mixed>, warnings: list<string>, raw: array<string, mixed>}  $result
     */
    protected function persistResult(PrintArtworkAnalysis $analysis, array $result): PrintArtworkAnalysis
    {
        $status = ColourAnalysisStatus::tryFrom($result['status']) ?? ColourAnalysisStatus::ManualReview;

        if ($status === ColourAnalysisStatus::Failed) {
            return $this->markFailed($analysis, implode(' ', $result['warnings'] ?? []));
        }

        if (in_array($status, [ColourAnalysisStatus::Unsupported, ColourAnalysisStatus::ManualReview], true) && $result['pages'] === []) {
            $analysis->update([
                'colour_analysis_status' => $status,
                'coverage_class' => CoverageClass::Unknown->value,
                'colour_analysis_warnings' => $result['warnings'] ?? [],
                'colour_analysis_raw' => $result['raw'] ?? [],
                'colour_analyzed_at' => now(),
            ]);

            return $analysis->fresh(['pages']);
        }

        foreach ($result['pages'] as $pageData) {
            $pageNumber = (int) ($pageData['page_number'] ?? 0);
            if ($pageNumber <= 0) {
                continue;
            }

            PrintArtworkPage::query()->updateOrCreate(
                [
                    'print_artwork_analysis_id' => $analysis->id,
                    'page_number' => $pageNumber,
                ],
                [
                    'company_id' => $analysis->company_id,
                    'rgb_coverage_percent' => $pageData['rgb_coverage_percent'] ?? null,
                    'cmyk_coverage_percent' => $pageData['cmyk_coverage_percent'] ?? null,
                    'cyan_coverage_percent' => $pageData['cyan_coverage_percent'] ?? null,
                    'magenta_coverage_percent' => $pageData['magenta_coverage_percent'] ?? null,
                    'yellow_coverage_percent' => $pageData['yellow_coverage_percent'] ?? null,
                    'black_coverage_percent' => $pageData['black_coverage_percent'] ?? null,
                    'white_area_percent' => $pageData['white_area_percent'] ?? null,
                    'transparent_area_percent' => $pageData['transparent_area_percent'] ?? null,
                    'dominant_colours' => $pageData['dominant_colours'] ?? null,
                    'coverage_class' => $pageData['coverage_class'] ?? null,
                    'colour_analysis_raw' => $pageData['colour_analysis_raw'] ?? null,
                ],
            );
        }

        $aggregate = $result['aggregate'];

        $analysis->update([
            'colour_analysis_status' => $status,
            'rgb_coverage_percent' => $aggregate['rgb_coverage_percent'] ?? null,
            'cmyk_coverage_percent' => $aggregate['cmyk_coverage_percent'] ?? null,
            'cyan_coverage_percent' => $aggregate['cyan_coverage_percent'] ?? null,
            'magenta_coverage_percent' => $aggregate['magenta_coverage_percent'] ?? null,
            'yellow_coverage_percent' => $aggregate['yellow_coverage_percent'] ?? null,
            'black_coverage_percent' => $aggregate['black_coverage_percent'] ?? null,
            'white_area_percent' => $aggregate['white_area_percent'] ?? null,
            'transparent_area_percent' => $aggregate['transparent_area_percent'] ?? null,
            'average_ink_density_percent' => $aggregate['average_ink_density_percent'] ?? null,
            'heavy_coverage_score' => $aggregate['heavy_coverage_score'] ?? null,
            'coverage_class' => $aggregate['coverage_class'] ?? CoverageClass::Unknown->value,
            'dominant_colours' => $aggregate['dominant_colours'] ?? $analysis->dominant_colours,
            'colour_analysis_warnings' => $result['warnings'] ?? [],
            'colour_analysis_raw' => array_merge($result['raw'] ?? [], [
                'channel_area_composition' => $aggregate['channel_area_composition'] ?? null,
            ]),
            'colour_analyzed_at' => now(),
        ]);

        return $analysis->fresh(['pages']);
    }

    protected function markFailed(PrintArtworkAnalysis $analysis, string $reason): PrintArtworkAnalysis
    {
        $analysis->update([
            'colour_analysis_status' => ColourAnalysisStatus::Failed,
            'colour_analysis_warnings' => array_merge($analysis->colour_analysis_warnings ?? [], [$reason]),
            'colour_analysis_raw' => array_merge($analysis->colour_analysis_raw ?? [], ['failure_reason' => $reason]),
        ]);

        return $analysis->fresh(['pages']);
    }
}

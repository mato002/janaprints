<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\ArtworkAnalysisSource;
use App\Enums\ArtworkAnalysisStatus;
use App\Enums\ColourAnalysisStatus;
use App\Enums\InkEstimationStatus;
use App\Enums\ProductionEstimationStatus;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintInkProfile;
use App\Models\PublicQuoteRequest;
use App\Support\PrintingIntelligence\CmykAreaComposition;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class QrArtworkAnalysisBridgeService
{
    public function __construct(
        protected ArtworkMetadataExtractionService $metadataExtraction,
        protected ColourAnalysisService $colourAnalysis,
        protected InkEstimationService $inkEstimation,
        protected ProductionEstimationService $productionEstimation,
        protected QuotationEstimationService $quotationEstimation,
        protected PrintingIntelligenceEnvironmentService $environment,
    ) {}

    /**
     * @param  array{
     *     force_rerun?: bool,
     *     steps?: list<string>,
     *     quantity?: int|null,
     *     uploaded_by?: int|null,
     * }  $options
     * @return array{analysis: PrintArtworkAnalysis, summary: array<string, mixed>, warnings: list<string>}
     */
    public function run(
        PublicQuoteRequest $quoteRequest,
        string $artworkFileId = 'primary',
        array $options = [],
    ): array {
        if (! config('printing_intelligence.artwork_analysis_enabled', true)) {
            abort(503, __('Artwork analysis is disabled.'));
        }

        $companyId = $this->resolveCompanyId($quoteRequest);
        $this->assertSameCompany($quoteRequest, $companyId);

        $file = $this->resolveArtworkFile($quoteRequest, $artworkFileId);
        $analysis = $this->resolveOrCreateAnalysis($quoteRequest, $file, $companyId, $options);

        $warnings = [];
        $steps = $options['steps'] ?? ['metadata', 'colour', 'ink', 'production'];
        $forceRerun = (bool) ($options['force_rerun'] ?? false);
        $quantity = max(1, (int) ($options['quantity'] ?? $this->parseQuantity($quoteRequest->quantity)));

        if (in_array('metadata', $steps, true)) {
            $analysis = $this->runMetadata($analysis, $forceRerun);
        }

        if (in_array('colour', $steps, true)) {
            $analysis = $this->runColour($analysis, $forceRerun, $warnings);
        }

        if (in_array('ink', $steps, true)) {
            $analysis = $this->runInk($analysis, $companyId, $warnings);
        }

        if (in_array('production', $steps, true)) {
            $analysis = $this->runProduction($analysis, $quantity, $warnings);
        }

        if (in_array('quotation', $steps, true)) {
            $analysis = $this->runQuotationEstimate($analysis, $quantity, $warnings);
        }

        $analysis = $analysis->fresh([
            'pages',
            'inkEstimates',
            'productionEstimate',
            'quotationEstimates',
        ]);

        return [
            'analysis' => $analysis,
            'summary' => $this->buildSummary($analysis),
            'warnings' => array_values(array_unique($warnings)),
        ];
    }

    public function assertSameCompany(PublicQuoteRequest $quoteRequest, int $companyId): void
    {
        if ($quoteRequest->company_id !== null
            && (int) $quoteRequest->company_id !== $companyId) {
            throw new AuthorizationException(__('This artwork belongs to another company.'));
        }
    }

    /**
     * @return array{
     *     disk: string,
     *     path: string,
     *     original_filename: string,
     *     source_model: class-string,
     *     source_id: int,
     *     extension: string,
     *     mime_type: ?string,
     *     size_bytes: int,
     *     hash: string,
     * }
     */
    public function resolveArtworkFile(PublicQuoteRequest $quoteRequest, string $artworkFileId): array
    {
        if ($artworkFileId !== 'primary') {
            throw ValidationException::withMessages([
                'artwork' => [__('Unsupported artwork file reference.')],
            ]);
        }

        if (! $quoteRequest->artwork_path) {
            throw ValidationException::withMessages([
                'artwork' => [__('No artwork file is attached to this quote request.')],
            ]);
        }

        $disk = (string) config('leads.artwork.disk', 'public');

        if (! Storage::disk($disk)->exists($quoteRequest->artwork_path)) {
            throw ValidationException::withMessages([
                'artwork' => [__('Artwork file is missing from storage.')],
            ]);
        }

        $extension = strtolower(pathinfo($quoteRequest->artwork_path, PATHINFO_EXTENSION));
        $this->assertSupportedExtension($extension);

        $absolutePath = Storage::disk($disk)->path($quoteRequest->artwork_path);
        $hash = hash_file('sha256', $absolutePath);

        if ($hash === false) {
            throw new RuntimeException('Unable to calculate artwork file hash.');
        }

        return [
            'disk' => $disk,
            'path' => $quoteRequest->artwork_path,
            'original_filename' => $quoteRequest->artwork_original_name ?? basename($quoteRequest->artwork_path),
            'source_model' => PublicQuoteRequest::class,
            'source_id' => $quoteRequest->id,
            'extension' => $extension,
            'mime_type' => Storage::disk($disk)->mimeType($quoteRequest->artwork_path) ?: null,
            'size_bytes' => (int) Storage::disk($disk)->size($quoteRequest->artwork_path),
            'hash' => $hash,
        ];
    }

    /**
     * @param  array<string, mixed>  $file
     * @param  array<string, mixed>  $options
     */
    /**
     * @param  array<string, mixed>  $options
     */
    public function ensureAnalysis(
        PublicQuoteRequest $quoteRequest,
        string $artworkFileId = 'primary',
        array $options = [],
    ): PrintArtworkAnalysis {
        $companyId = $this->resolveCompanyId($quoteRequest);
        $file = $this->resolveArtworkFile($quoteRequest, $artworkFileId);

        return $this->resolveOrCreateAnalysis($quoteRequest, $file, $companyId, $options);
    }

    public function resolveOrCreateAnalysis(
        PublicQuoteRequest $quoteRequest,
        array $file,
        int $companyId,
        array $options = [],
    ): PrintArtworkAnalysis {
        $existing = PrintArtworkAnalysis::query()
            ->where('company_id', $companyId)
            ->where('public_quote_request_id', $quoteRequest->id)
            ->where('source_file_model', $file['source_model'])
            ->where('source_file_id', $file['source_id'])
            ->whereNull('deleted_at')
            ->whereNotIn('analysis_status', [ArtworkAnalysisStatus::Failed->value])
            ->latest('id')
            ->first();

        if ($existing !== null) {
            $existing->update(array_filter([
                'quotation_id' => $quoteRequest->quotation_id ?? $existing->quotation_id,
                'branch_id' => $quoteRequest->branch_id ?? $existing->branch_id,
            ]));

            return $existing;
        }

        $branchId = $quoteRequest->branch_id ?? tenant()->branchId() ?? auth()->user()?->default_branch_id;

        return PrintArtworkAnalysis::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'quotation_id' => $quoteRequest->quotation_id,
            'public_quote_request_id' => $quoteRequest->id,
            'uploaded_by' => $options['uploaded_by'] ?? auth()->id(),
            'original_filename' => $file['original_filename'],
            'stored_filename' => basename($file['path']),
            'file_path' => $file['path'],
            'disk' => $file['disk'],
            'mime_type' => $file['mime_type'],
            'file_extension' => $file['extension'],
            'file_size_bytes' => $file['size_bytes'],
            'file_hash' => $file['hash'],
            'analysis_status' => ArtworkAnalysisStatus::Pending,
            'analysis_source' => ArtworkAnalysisSource::QuoteRequest,
            'source_file_model' => $file['source_model'],
            'source_file_id' => $file['source_id'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function buildSummary(PrintArtworkAnalysis $analysis): array
    {
        $analysis->loadMissing([
            'inkEstimates.inkProfile',
            'productionEstimate.machineProfile',
            'quotationEstimates',
        ]);

        $inkEstimate = $analysis->inkEstimates->first();
        $productionEstimate = $analysis->productionEstimate;
        $quotationEstimate = $analysis->quotationEstimates->first();
        $machineName = $productionEstimate?->machineProfile?->machine_code;

        if ($machineName === null && $productionEstimate?->metadata) {
            $selectedMachine = $productionEstimate->metadata['selected_machine'] ?? null;
            $machineName = is_array($selectedMachine)
                ? ($selectedMachine['machine_code'] ?? $selectedMachine['machine_name'] ?? null)
                : ($productionEstimate->metadata['machine_name'] ?? $productionEstimate->metadata['selected_machine_name'] ?? null);
        }

        $channelComposition = $this->resolveChannelAreaComposition($analysis);
        $detectedColours = collect($analysis->dominant_colours ?? [])
            ->map(fn ($colour) => [
                'hex' => $colour['hex'] ?? null,
                'percent' => $colour['percent'] ?? null,
            ])
            ->filter(fn (array $colour) => $colour['hex'] !== null)
            ->values()
            ->all();

        return [
            'id' => $analysis->id,
            'analysis_status' => $analysis->analysis_status?->value,
            'analysis_status_label' => $analysis->analysis_status?->label(),
            'colour_analysis_status' => $analysis->colour_analysis_status?->value,
            'colour_analysis_status_label' => $analysis->colour_analysis_status?->label(),
            'file_extension' => $analysis->file_extension,
            'page_count' => $analysis->page_count,
            'width_mm' => $analysis->width_mm,
            'height_mm' => $analysis->height_mm,
            'dimensions' => $analysis->width_mm && $analysis->height_mm
                ? number_format((float) $analysis->width_mm, 1).' × '.number_format((float) $analysis->height_mm, 1).' mm'
                : null,
            'cmyk_coverage_percent' => $analysis->cmyk_coverage_percent,
            'coverage_class' => $analysis->coverage_class?->value,
            'coverage_class_label' => $analysis->coverage_class?->label(),
            'estimated_ink_ml' => $inkEstimate?->estimated_total_ml,
            'estimated_ink_cost' => $inkEstimate?->estimated_ink_cost,
            'recommended_machine' => $machineName,
            'file_info' => [
                'original_filename' => $analysis->original_filename,
                'file_extension' => $analysis->file_extension,
                'mime_type' => $analysis->mime_type,
                'file_size_bytes' => $analysis->file_size_bytes,
                'file_size_label' => $analysis->file_size_bytes > 0
                    ? number_format($analysis->file_size_bytes / 1024, 1).' KB'
                    : null,
                'page_count' => $analysis->page_count,
                'dimensions' => $analysis->width_mm && $analysis->height_mm
                    ? number_format((float) $analysis->width_mm, 1).' × '.number_format((float) $analysis->height_mm, 1).' mm'
                    : null,
                'resolution_dpi' => $analysis->resolution_dpi,
                'colour_mode' => $analysis->colour_mode,
                'colour_analyzed_at' => $analysis->colour_analyzed_at?->format('Y-m-d H:i'),
                'heavy_coverage_score' => $analysis->heavy_coverage_score,
            ],
            'dominant_colours' => $detectedColours,
            'detected_colours' => $detectedColours,
            'detected_colours_total_percent' => round(collect($detectedColours)->sum('percent'), 2),
            'colour_analysis_warnings' => array_values($analysis->colour_analysis_warnings ?? []),
            'ink_coverage' => [
                'cmyk_coverage_percent' => $analysis->cmyk_coverage_percent,
                'rgb_coverage_percent' => $analysis->rgb_coverage_percent,
                'white_area_percent' => $analysis->white_area_percent,
                'transparent_area_percent' => $analysis->transparent_area_percent,
                'average_ink_density_percent' => $analysis->average_ink_density_percent,
                'coverage_class_label' => $analysis->coverage_class?->label(),
                'cmyk_breakdown' => [
                    'cyan' => $channelComposition['cyan'] ?? null,
                    'magenta' => $channelComposition['magenta'] ?? null,
                    'yellow' => $channelComposition['yellow'] ?? null,
                    'black' => $channelComposition['black'] ?? null,
                    'white' => $channelComposition['white'] ?? null,
                    'transparent' => $channelComposition['transparent'] ?? null,
                    'total' => $channelComposition['total'] ?? 100,
                ],
            ],
            'ink_estimate' => $inkEstimate ? [
                'status_label' => $inkEstimate->estimation_status?->label(),
                'estimated_total_ml' => $inkEstimate->estimated_total_ml,
                'estimated_ink_cost' => $inkEstimate->estimated_ink_cost,
                'confidence_score' => $inkEstimate->confidence_score,
                'ink_profile_name' => $inkEstimate->inkProfile?->name,
                'cmyk_ml' => [
                    'cyan' => $inkEstimate->estimated_cyan_ml,
                    'magenta' => $inkEstimate->estimated_magenta_ml,
                    'yellow' => $inkEstimate->estimated_yellow_ml,
                    'black' => $inkEstimate->estimated_black_ml,
                ],
                'warnings' => array_values($inkEstimate->warnings ?? []),
            ] : null,
            'production_estimate' => $productionEstimate ? [
                'status_label' => $productionEstimate->estimation_status?->label(),
                'machine_code' => $machineName,
                'quantity' => $productionEstimate->quantity,
                'estimated_run_hours' => $productionEstimate->estimated_run_hours,
                'estimated_total_production_cost' => $productionEstimate->estimated_total_production_cost,
                'estimated_machine_cost' => $productionEstimate->estimated_machine_cost,
                'estimated_labour_cost' => $productionEstimate->estimated_labour_cost,
                'estimated_ink_cost' => $productionEstimate->estimated_ink_cost,
                'estimated_setup_cost' => $productionEstimate->estimated_setup_cost,
                'estimated_overhead_cost' => $productionEstimate->estimated_overhead_cost,
                'confidence_score' => $productionEstimate->confidence_score,
                'selection_score' => $productionEstimate->selection_score,
                'warnings' => array_values($productionEstimate->warnings ?? []),
            ] : null,
            'quotation_estimate' => $quotationEstimate ? [
                'id' => $quotationEstimate->id,
                'status_label' => $quotationEstimate->estimation_status?->label(),
                'can_apply' => config('printing_intelligence.allow_apply_to_quotation', true)
                    && in_array($quotationEstimate->estimation_status, [
                        \App\Enums\QuotationEstimationStatus::Completed,
                        \App\Enums\QuotationEstimationStatus::ManualReview,
                    ], true)
                    && $analysis->quotation_id !== null,
                'quantity' => $quotationEstimate->quantity,
                'estimated_total_cost' => $quotationEstimate->estimated_total_cost,
                'minimum_selling_price' => $quotationEstimate->minimum_selling_price,
                'recommended_selling_price' => $quotationEstimate->recommended_selling_price,
                'expected_margin_percent' => $quotationEstimate->expected_margin_percent,
                'confidence_score' => $quotationEstimate->confidence_score,
                'estimated_material_cost' => $quotationEstimate->estimated_material_cost,
                'estimated_ink_cost' => $quotationEstimate->estimated_ink_cost,
                'estimated_machine_cost' => $quotationEstimate->estimated_machine_cost,
                'estimated_labour_cost' => $quotationEstimate->estimated_labour_cost,
                'estimated_electricity_cost' => $quotationEstimate->estimated_electricity_cost,
                'estimated_overhead_cost' => $quotationEstimate->estimated_overhead_cost,
                'estimated_wastage_cost' => $quotationEstimate->estimated_wastage_cost,
                'warnings' => array_values($quotationEstimate->warnings ?? []),
            ] : null,
            'warnings' => array_values(array_unique(array_merge(
                $analysis->warnings ?? [],
                $inkEstimate?->warnings ?? [],
                $productionEstimate?->warnings ?? [],
                $quotationEstimate?->warnings ?? [],
            ))),
            'show_url' => route('admin.printing-intelligence.artwork-analysis.show', $analysis),
            'environment' => $this->environment->diagnostics(),
        ];
    }

    public function findLinkedAnalysis(PublicQuoteRequest $quoteRequest, string $artworkFileId = 'primary'): ?PrintArtworkAnalysis
    {
        if ($artworkFileId !== 'primary' || ! $quoteRequest->artwork_path) {
            return null;
        }

        return PrintArtworkAnalysis::query()
            ->where('public_quote_request_id', $quoteRequest->id)
            ->where('source_file_model', PublicQuoteRequest::class)
            ->where('source_file_id', $quoteRequest->id)
            ->whereNull('deleted_at')
            ->latest('id')
            ->first();
    }

    public function isSupportedArtwork(PublicQuoteRequest $quoteRequest): bool
    {
        if (! $quoteRequest->artwork_path) {
            return false;
        }

        $disk = (string) config('leads.artwork.disk', 'public');

        if (! Storage::disk($disk)->exists($quoteRequest->artwork_path)) {
            return false;
        }

        $extension = strtolower(pathinfo($quoteRequest->artwork_path, PATHINFO_EXTENSION));
        $allowed = array_map('strtolower', config('printing_intelligence.allowed_artwork_extensions', []));

        return in_array($extension, $allowed, true);
    }

    protected function runMetadata(PrintArtworkAnalysis $analysis, bool $forceRerun): PrintArtworkAnalysis
    {
        if (! $forceRerun && $analysis->analysis_status !== ArtworkAnalysisStatus::Pending) {
            return $analysis;
        }

        $analysis->update(['analysis_status' => ArtworkAnalysisStatus::Processing]);

        try {
            return $this->metadataExtraction->extract($analysis);
        } catch (Throwable $exception) {
            $analysis->update([
                'analysis_status' => ArtworkAnalysisStatus::Failed,
                'errors' => [__('Analysis failed: :message', ['message' => $exception->getMessage()])],
                'failed_at' => now(),
                'failure_reason' => $exception->getMessage(),
            ]);

            return $analysis->fresh();
        }
    }

    /**
     * @param  list<string>  $warnings
     */
    protected function runColour(PrintArtworkAnalysis $analysis, bool $forceRerun, array &$warnings): PrintArtworkAnalysis
    {
        if (! config('printing_intelligence.colour_analysis_enabled', true)) {
            $warnings[] = __('Colour analysis is disabled; step skipped.');

            return $analysis;
        }

        if (! $forceRerun && in_array($analysis->colour_analysis_status, [
            ColourAnalysisStatus::Completed,
            ColourAnalysisStatus::ManualReview,
        ], true)) {
            return $analysis;
        }

        if ($analysis->colour_analysis_status === ColourAnalysisStatus::Processing) {
            $warnings[] = __('Colour analysis is already in progress.');

            return $analysis;
        }

        if (! in_array($analysis->analysis_status, [
            ArtworkAnalysisStatus::Completed,
            ArtworkAnalysisStatus::ManualReview,
        ], true)) {
            $warnings[] = __('Metadata analysis must complete before colour analysis.');

            return $analysis;
        }

        return $this->colourAnalysis->analyze($analysis);
    }

    /**
     * @param  list<string>  $warnings
     */
    protected function runInk(PrintArtworkAnalysis $analysis, int $companyId, array &$warnings): PrintArtworkAnalysis
    {
        if (! config('printing_intelligence.ink_costing_enabled', true)) {
            $warnings[] = __('Ink costing is disabled; ink estimate skipped.');

            return $analysis;
        }

        $profile = PrintInkProfile::query()
            ->where('company_id', $companyId)
            ->where('active', true)
            ->orderBy('id')
            ->first();

        if ($profile === null) {
            $warnings[] = __('No active ink profile available; ink estimate skipped.');

            return $analysis;
        }

        if ($analysis->inkEstimates()->where('estimation_status', InkEstimationStatus::Processing)->exists()) {
            $warnings[] = __('Ink estimation is already in progress.');

            return $analysis;
        }

        if (! in_array($analysis->colour_analysis_status, [
            ColourAnalysisStatus::Completed,
            ColourAnalysisStatus::ManualReview,
        ], true)) {
            $warnings[] = __('Colour analysis must be completed before ink estimation.');

            return $analysis;
        }

        $this->inkEstimation->estimate($analysis, $profile);

        return $analysis->fresh();
    }

    /**
     * @param  list<string>  $warnings
     */
    protected function runProduction(PrintArtworkAnalysis $analysis, int $quantity, array &$warnings): PrintArtworkAnalysis
    {
        if (! config('printing_intelligence.production_costing_enabled', true)) {
            $warnings[] = __('Production costing is disabled; machine estimate skipped.');

            return $analysis;
        }

        if ($analysis->productionEstimate?->estimation_status === ProductionEstimationStatus::Processing) {
            $warnings[] = __('Production estimation is already in progress.');

            return $analysis;
        }

        $estimate = $this->productionEstimation->estimate($analysis, null, $quantity);

        if ($estimate->estimation_status === ProductionEstimationStatus::ManualReview) {
            $estimateWarnings = $estimate->warnings ?? [];

            if (collect($estimateWarnings)->contains(fn ($warning) => str_contains((string) $warning, 'No eligible production machines'))) {
                $warnings[] = __('No production machine profile available; machine estimate skipped.');
            } else {
                $warnings = array_merge($warnings, $estimateWarnings);
            }
        }

        return $analysis->fresh();
    }

    /**
     * @param  list<string>  $warnings
     */
    protected function runQuotationEstimate(PrintArtworkAnalysis $analysis, int $quantity, array &$warnings): PrintArtworkAnalysis
    {
        if (! config('printing_intelligence.quotation_estimation_enabled', true)) {
            $warnings[] = __('Quotation estimation is disabled; step skipped.');

            return $analysis;
        }

        try {
            $this->quotationEstimation->estimate($analysis, ['quantity' => $quantity]);
        } catch (Throwable $exception) {
            $warnings[] = $exception->getMessage();
        }

        return $analysis->fresh();
    }

    /**
     * @return array{
     *     cyan: float,
     *     magenta: float,
     *     yellow: float,
     *     black: float,
     *     white: float,
     *     transparent: float,
     *     total: float,
     * }
     */
    protected function resolveChannelAreaComposition(PrintArtworkAnalysis $analysis): array
    {
        $fromRaw = $analysis->colour_analysis_raw['channel_area_composition'] ?? null;

        if (is_array($fromRaw) && isset($fromRaw['total'])) {
            return CmykAreaComposition::rebalanceToHundred([
                'cyan' => (float) ($fromRaw['cyan'] ?? 0),
                'magenta' => (float) ($fromRaw['magenta'] ?? 0),
                'yellow' => (float) ($fromRaw['yellow'] ?? 0),
                'black' => (float) ($fromRaw['black'] ?? 0),
                'white' => (float) ($fromRaw['white'] ?? 0),
                'transparent' => (float) ($fromRaw['transparent'] ?? 0),
            ]);
        }

        return CmykAreaComposition::fromChannelCoverage(
            (float) ($analysis->cyan_coverage_percent ?? 0),
            (float) ($analysis->magenta_coverage_percent ?? 0),
            (float) ($analysis->yellow_coverage_percent ?? 0),
            (float) ($analysis->black_coverage_percent ?? 0),
            (float) ($analysis->white_area_percent ?? 0),
            (float) ($analysis->transparent_area_percent ?? 0),
        );
    }

    protected function resolveCompanyId(PublicQuoteRequest $quoteRequest): int
    {
        $tenantCompanyId = tenant()->companyId() ?? auth()->user()?->company_id;

        if ($tenantCompanyId === null) {
            throw new AuthorizationException(__('Active company context is required.'));
        }

        return (int) $tenantCompanyId;
    }

    protected function parseQuantity(?string $quantity): int
    {
        if ($quantity === null || $quantity === '') {
            return 1;
        }

        if (preg_match('/\d+/', $quantity, $matches) === 1) {
            return max(1, (int) $matches[0]);
        }

        return 1;
    }

    protected function assertSupportedExtension(string $extension): void
    {
        $allowed = array_map('strtolower', config('printing_intelligence.allowed_artwork_extensions', []));

        if (! in_array($extension, $allowed, true)) {
            throw ValidationException::withMessages([
                'artwork' => [__('Unsupported artwork file type for Printing Intelligence analysis. Allowed: :types.', [
                    'types' => strtoupper(implode(', ', $allowed)),
                ])],
            ]);
        }
    }
}

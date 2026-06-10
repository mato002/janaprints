<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\ArtworkAnalysisStatus;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintArtworkPage;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Throwable;

class ArtworkMetadataExtractionService
{
    /**
     * Extract metadata and persist page rows for an analysis record.
     */
    public function extract(PrintArtworkAnalysis $analysis): PrintArtworkAnalysis
    {
        $analysis->pages()->delete();

        $absolutePath = Storage::disk($analysis->disk)->path($analysis->file_path);
        $warnings = [];
        $metadata = [
            'extracted_at' => now()->toIso8601String(),
            'extractor_version' => 'pi1',
        ];

        $extension = strtolower((string) $analysis->file_extension);

        try {
            if ($this->isImageExtension($extension)) {
                return $this->extractImageMetadata($analysis, $absolutePath, $metadata, $warnings);
            }

            if ($extension === 'pdf') {
                return $this->extractPdfMetadata($analysis, $absolutePath, $metadata, $warnings);
            }

            $warnings[] = __('No metadata extractor configured for this file type.');
            $analysis->update([
                'analysis_status' => ArtworkAnalysisStatus::ManualReview,
                'metadata' => $metadata,
                'warnings' => $warnings,
                'analyzed_at' => now(),
            ]);

            return $analysis->fresh(['pages']);
        } catch (Throwable $exception) {
            $analysis->update([
                'analysis_status' => ArtworkAnalysisStatus::Failed,
                'metadata' => $metadata,
                'warnings' => $warnings,
                'errors' => [__('Metadata extraction failed: :message', ['message' => $exception->getMessage()])],
                'failed_at' => now(),
                'failure_reason' => $exception->getMessage(),
            ]);

            return $analysis->fresh(['pages']);
        }
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @param  list<string>  $warnings
     */
    protected function extractImageMetadata(
        PrintArtworkAnalysis $analysis,
        string $absolutePath,
        array $metadata,
        array $warnings,
    ): PrintArtworkAnalysis {
        if (! config('printing_intelligence.image_metadata_enabled', true)) {
            $warnings[] = __('Image metadata extraction is disabled by configuration.');
            $analysis->update([
                'analysis_status' => ArtworkAnalysisStatus::ManualReview,
                'metadata' => $metadata,
                'warnings' => $warnings,
                'analyzed_at' => now(),
            ]);

            return $analysis->fresh(['pages']);
        }

        $size = @getimagesize($absolutePath);

        if ($size === false) {
            $warnings[] = __('Unable to read image dimensions.');
            $analysis->update([
                'analysis_status' => ArtworkAnalysisStatus::ManualReview,
                'metadata' => $metadata,
                'warnings' => $warnings,
                'analyzed_at' => now(),
            ]);

            return $analysis->fresh(['pages']);
        }

        [$widthPx, $heightPx] = [$size[0], $size[1]];
        $metadata['width_px'] = $widthPx;
        $metadata['height_px'] = $heightPx;
        $metadata['image_type'] = $size[2] ?? null;
        $metadata['mime_from_probe'] = $size['mime'] ?? null;

        $dpi = $this->resolveImageDpi($absolutePath, $size);
        $widthMm = null;
        $heightMm = null;
        $areaSqM = null;

        if ($dpi !== null && $dpi > 0) {
            $widthMm = round(($widthPx / $dpi) * 25.4, 3);
            $heightMm = round(($heightPx / $dpi) * 25.4, 3);
            $areaSqM = round(($widthMm / 1000) * ($heightMm / 1000), 6);
        } else {
            $warnings[] = __('Image DPI unavailable; pixel dimensions stored in metadata.');
        }

        $colourMode = $this->resolveColourMode($size);
        $hasTransparency = $this->detectTransparency($absolutePath, strtolower((string) $analysis->file_extension));

        PrintArtworkPage::query()->create([
            'company_id' => $analysis->company_id,
            'print_artwork_analysis_id' => $analysis->id,
            'page_number' => 1,
            'width_mm' => $widthMm,
            'height_mm' => $heightMm,
            'area_square_m' => $areaSqM,
            'resolution_dpi' => $dpi,
            'colour_mode' => $colourMode,
            'metadata' => [
                'width_px' => $widthPx,
                'height_px' => $heightPx,
            ],
            'warnings' => $dpi === null ? [__('DPI unavailable for page 1.')] : [],
        ]);

        $analysis->update([
            'page_count' => 1,
            'width_mm' => $widthMm,
            'height_mm' => $heightMm,
            'area_square_m' => $areaSqM,
            'resolution_dpi' => $dpi,
            'colour_mode' => $colourMode,
            'has_transparency' => $hasTransparency,
            'metadata' => $metadata,
            'warnings' => $warnings,
            'analysis_status' => $warnings === [] ? ArtworkAnalysisStatus::Completed : ArtworkAnalysisStatus::ManualReview,
            'analyzed_at' => now(),
        ]);

        return $analysis->fresh(['pages']);
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @param  list<string>  $warnings
     */
    protected function extractPdfMetadata(
        PrintArtworkAnalysis $analysis,
        string $absolutePath,
        array $metadata,
        array $warnings,
    ): PrintArtworkAnalysis {
        $metadata['file_size_bytes'] = $analysis->file_size_bytes;
        $metadata['mime_type'] = $analysis->mime_type;

        if (! config('printing_intelligence.pdf_metadata_tool_enabled', true) || ! $this->pdfInfoAvailable()) {
            $warnings[] = __('PDF page dimensions require server PDF metadata tool (pdfinfo/poppler).');

            $analysis->update([
                'analysis_status' => ArtworkAnalysisStatus::ManualReview,
                'metadata' => $metadata,
                'warnings' => $warnings,
                'analyzed_at' => now(),
            ]);

            return $analysis->fresh(['pages']);
        }

        $info = $this->runPdfInfo($absolutePath);

        if ($info === null) {
            $warnings[] = __('PDF metadata tool did not return usable output.');
            $analysis->update([
                'analysis_status' => ArtworkAnalysisStatus::ManualReview,
                'metadata' => $metadata,
                'warnings' => $warnings,
                'analyzed_at' => now(),
            ]);

            return $analysis->fresh(['pages']);
        }

        $metadata['pdfinfo'] = $info;
        $pageCount = isset($info['Pages']) ? (int) $info['Pages'] : null;

        $pageSize = $info['Page size'] ?? null;
        [$widthMm, $heightMm] = $this->parsePdfPageSizeMm($pageSize);
        $areaSqM = ($widthMm !== null && $heightMm !== null)
            ? round(($widthMm / 1000) * ($heightMm / 1000), 6)
            : null;

        if ($pageCount !== null && $pageCount > 0) {
            for ($page = 1; $page <= $pageCount; $page++) {
                PrintArtworkPage::query()->create([
                    'company_id' => $analysis->company_id,
                    'print_artwork_analysis_id' => $analysis->id,
                    'page_number' => $page,
                    'width_mm' => $widthMm,
                    'height_mm' => $heightMm,
                    'area_square_m' => $areaSqM,
                    'metadata' => [
                        'page_size_raw' => $pageSize,
                    ],
                    'warnings' => $widthMm === null ? [__('Page dimensions unavailable from pdfinfo.')] : [],
                ]);
            }
        } elseif ($widthMm !== null) {
            PrintArtworkPage::query()->create([
                'company_id' => $analysis->company_id,
                'print_artwork_analysis_id' => $analysis->id,
                'page_number' => 1,
                'width_mm' => $widthMm,
                'height_mm' => $heightMm,
                'area_square_m' => $areaSqM,
                'metadata' => ['page_size_raw' => $pageSize],
            ]);
            $pageCount = 1;
        } else {
            $warnings[] = __('PDF page count and dimensions could not be determined.');
        }

        $analysis->update([
            'page_count' => $pageCount,
            'width_mm' => $widthMm,
            'height_mm' => $heightMm,
            'area_square_m' => $areaSqM,
            'metadata' => $metadata,
            'warnings' => $warnings,
            'analysis_status' => ($warnings === [] && $pageCount !== null)
                ? ArtworkAnalysisStatus::Completed
                : ArtworkAnalysisStatus::ManualReview,
            'analyzed_at' => now(),
        ]);

        return $analysis->fresh(['pages']);
    }

    /**
     * @param  array<int|string, mixed>  $size
     */
    protected function resolveImageDpi(string $absolutePath, array $size): ?float
    {
        if (($size['mime'] ?? '') === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($absolutePath);
            if (is_array($exif)) {
                if (! empty($exif['XResolution']) && is_numeric($exif['XResolution'])) {
                    return (float) $exif['XResolution'];
                }
                if (! empty($exif['ResolutionUnit']) && ! empty($exif['YResolution'])) {
                    $dpi = (float) $exif['YResolution'];
                    if ((int) $exif['ResolutionUnit'] === 3) {
                        return round($dpi * 2.54, 2);
                    }

                    return $dpi;
                }
            }
        }

        if (isset($size[0], $size[1]) && function_exists('imagecreatefromstring')) {
            $contents = @file_get_contents($absolutePath);
            if ($contents !== false) {
                $image = @imagecreatefromstring($contents);
                if ($image !== false) {
                    $resX = imageresolution($image)[0] ?? 0;
                    imagedestroy($image);
                    if ($resX > 0) {
                        return (float) $resX;
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param  array<int|string, mixed>  $size
     */
    protected function resolveColourMode(array $size): ?string
    {
        $channels = $size['channels'] ?? null;

        return match (true) {
            $channels === 4 => 'CMYK',
            $channels === 3 => 'RGB',
            $channels === 1 => 'Grayscale',
            default => null,
        };
    }

    protected function detectTransparency(string $absolutePath, string $extension): ?bool
    {
        if (! in_array($extension, ['png', 'webp', 'gif', 'tiff', 'tif'], true)) {
            return false;
        }

        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $contents = @file_get_contents($absolutePath);
        if ($contents === false) {
            return null;
        }

        $image = @imagecreatefromstring($contents);
        if ($image === false) {
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $hasAlpha = false;

        for ($x = 0; $x < min($width, 20); $x++) {
            for ($y = 0; $y < min($height, 20); $y++) {
                $rgba = imagecolorat($image, $x, $y);
                $alpha = ($rgba & 0x7F000000) >> 24;
                if ($alpha > 0) {
                    $hasAlpha = true;
                    break 2;
                }
            }
        }

        imagedestroy($image);

        return $hasAlpha;
    }

    protected function isImageExtension(string $extension): bool
    {
        return in_array($extension, ['jpg', 'jpeg', 'png', 'tif', 'tiff', 'webp'], true);
    }

    protected function pdfInfoAvailable(): bool
    {
        $process = new Process(['pdfinfo', '-v']);
        $process->run();

        return $process->isSuccessful();
    }

    /**
     * @return array<string, string>|null
     */
    protected function runPdfInfo(string $absolutePath): ?array
    {
        $process = new Process(['pdfinfo', $absolutePath]);
        $process->setTimeout(30);
        $process->run();

        if (! $process->isSuccessful()) {
            return null;
        }

        $parsed = [];
        foreach (explode("\n", trim($process->getOutput())) as $line) {
            if (! str_contains($line, ':')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode(':', $line, 2));
            $parsed[$key] = $value;
        }

        return $parsed === [] ? null : $parsed;
    }

    /**
     * @return array{0: float|null, 1: float|null}
     */
    protected function parsePdfPageSizeMm(?string $pageSize): array
    {
        if ($pageSize === null) {
            return [null, null];
        }

        if (preg_match('/([\d.]+)\s*x\s*([\d.]+)\s*pts/i', $pageSize, $matches)) {
            $widthPts = (float) $matches[1];
            $heightPts = (float) $matches[2];

            return [
                round($widthPts * 25.4 / 72, 3),
                round($heightPts * 25.4 / 72, 3),
            ];
        }

        if (preg_match('/([\d.]+)\s*x\s*([\d.]+)\s*mm/i', $pageSize, $matches)) {
            return [(float) $matches[1], (float) $matches[2]];
        }

        return [null, null];
    }
}

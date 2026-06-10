<?php

namespace App\Services\PrintingIntelligence;

use App\Support\PrintingIntelligence\CmykAreaComposition;
use App\Support\PrintingIntelligence\CoverageClassifier;

class ImageColourAnalyzer
{
    public function __construct(
        protected CoverageClassifier $classifier,
    ) {}

    /**
     * @return array{
     *     status: string,
     *     pages: list<array<string, mixed>>,
     *     aggregate: array<string, mixed>,
     *     warnings: list<string>,
     *     raw: array<string, mixed>
     * }
     */
    public function analyze(string $absolutePath, ?float $resolutionDpi = null, ?bool $hasTransparencyHint = null): array
    {
        if (! function_exists('imagecreatefromstring')) {
            return $this->unsupported(__('GD extension unavailable for image colour analysis.'));
        }

        $contents = @file_get_contents($absolutePath);
        if ($contents === false) {
            return $this->failed(__('Unable to read image file.'));
        }

        $image = @imagecreatefromstring($contents);
        if ($image === false) {
            return $this->unsupported(__('Unsupported or corrupt image for colour analysis.'));
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $totalPixels = $width * $height;

        if ($totalPixels <= 0) {
            imagedestroy($image);

            return $this->failed(__('Image has no readable pixels.'));
        }

        $maxPixels = (int) config('printing_intelligence.pixel_sampling_max_pixels', 500000);
        $stride = (int) max(1, ceil(sqrt($totalPixels / max(1, $maxPixels))));
        $whiteThreshold = (int) config('printing_intelligence.coverage_white_threshold_rgb', 245);

        $sampled = 0;
        $whiteCount = 0;
        $transparentCount = 0;
        $inkedCount = 0;
        $sumC = $sumM = $sumY = $sumK = 0.0;
        $areaC = $areaM = $areaY = $areaK = 0.0;
        $colourBuckets = [];
        $bucketDivisor = max(1, (int) config('printing_intelligence.colour_bucket_divisor', 32));

        for ($y = 0; $y < $height; $y += $stride) {
            for ($x = 0; $x < $width; $x += $stride) {
                $rgba = imagecolorat($image, $x, $y);
                $alpha = ($rgba & 0x7F000000) >> 24;
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;

                $sampled++;

                if ($alpha > 0) {
                    $transparentCount++;

                    continue;
                }

                if ($r >= $whiteThreshold && $g >= $whiteThreshold && $b >= $whiteThreshold) {
                    $whiteCount++;

                    continue;
                }

                $inkedCount++;
                $cmyk = $this->rgbToCmyk($r, $g, $b);
                $sumC += $cmyk['c'];
                $sumM += $cmyk['m'];
                $sumY += $cmyk['y'];
                $sumK += $cmyk['k'];

                $inkTotal = $cmyk['c'] + $cmyk['m'] + $cmyk['y'] + $cmyk['k'];
                if ($inkTotal > 0) {
                    $areaC += $cmyk['c'] / $inkTotal;
                    $areaM += $cmyk['m'] / $inkTotal;
                    $areaY += $cmyk['y'] / $inkTotal;
                    $areaK += $cmyk['k'] / $inkTotal;
                }

                $bucketKey = sprintf(
                    '%02d-%02d-%02d',
                    intdiv($r, $bucketDivisor),
                    intdiv($g, $bucketDivisor),
                    intdiv($b, $bucketDivisor),
                );
                $colourBuckets[$bucketKey] = ($colourBuckets[$bucketKey] ?? 0) + 1;
            }
        }

        imagedestroy($image);

        if ($sampled === 0) {
            return $this->failed(__('No pixels sampled from image.'));
        }

        $whitePercent = round(($whiteCount / $sampled) * 100, 3);
        $transparentPercent = round(($transparentCount / $sampled) * 100, 3);
        $rgbCoverage = round(($inkedCount / $sampled) * 100, 3);

        $cyan = $magenta = $yellow = $black = 0.0;
        if ($inkedCount > 0) {
            $cyan = round($sumC / $inkedCount, 3);
            $magenta = round($sumM / $inkedCount, 3);
            $yellow = round($sumY / $inkedCount, 3);
            $black = round($sumK / $inkedCount, 3);
        }

        $cmykCoverage = round((($cyan + $magenta + $yellow + $black) / 4), 3);
        $averageInkDensity = $cmykCoverage;

        $pixelShare = 100 / $sampled;
        $channelAreaComposition = CmykAreaComposition::rebalanceToHundred([
            'cyan' => $areaC * $pixelShare,
            'magenta' => $areaM * $pixelShare,
            'yellow' => $areaY * $pixelShare,
            'black' => $areaK * $pixelShare,
            'white' => $whitePercent,
            'transparent' => $transparentPercent,
        ]);
        $dominantColours = $this->detectedColoursFromBuckets($colourBuckets, $sampled, $bucketDivisor);
        $hasTransparency = $hasTransparencyHint ?? ($transparentPercent > 0);

        $warnings = [];
        if ($resolutionDpi === null || $resolutionDpi <= 0) {
            $warnings[] = __('Image DPI unavailable; coverage based on pixel sampling only.');
        }

        $metrics = [
            'rgb_coverage_percent' => $rgbCoverage,
            'cmyk_coverage_percent' => $cmykCoverage,
            'cyan_coverage_percent' => $cyan,
            'magenta_coverage_percent' => $magenta,
            'yellow_coverage_percent' => $yellow,
            'black_coverage_percent' => $black,
            'white_area_percent' => $whitePercent,
            'transparent_area_percent' => $transparentPercent,
            'average_ink_density_percent' => $averageInkDensity,
            'resolution_dpi' => $resolutionDpi,
            'has_transparency' => $hasTransparency,
        ];

        $classification = $this->classifier->classify($metrics);
        $warnings = array_merge($warnings, $classification['warnings']);

        $page = array_merge($metrics, [
            'page_number' => 1,
            'dominant_colours' => $dominantColours,
            'coverage_class' => $classification['coverage_class']->value,
            'colour_analysis_raw' => [
                'sampled_pixels' => $sampled,
                'stride' => $stride,
                'width_px' => $width,
                'height_px' => $height,
                'bucket_divisor' => $bucketDivisor,
                'channel_area_composition' => $channelAreaComposition,
            ],
        ]);

        return [
            'status' => $warnings === [] ? 'completed' : 'manual_review',
            'pages' => [$page],
            'aggregate' => array_merge($metrics, [
                'dominant_colours' => $dominantColours,
                'channel_area_composition' => $channelAreaComposition,
                'coverage_class' => $classification['coverage_class']->value,
                'heavy_coverage_score' => $classification['heavy_coverage_score'],
            ]),
            'warnings' => $warnings,
            'raw' => [
                'analyzer' => 'image',
                'sampled_pixels' => $sampled,
                'stride' => $stride,
                'bucket_divisor' => $bucketDivisor,
                'channel_area_composition' => $channelAreaComposition,
                'detected_colour_count' => count($dominantColours),
            ],
        ];
    }

    /**
     * @return array{c: float, m: float, y: float, k: float}
     */
    public function rgbToCmyk(int $r, int $g, int $b): array
    {
        $rNorm = $r / 255;
        $gNorm = $g / 255;
        $bNorm = $b / 255;

        $k = 1 - max($rNorm, $gNorm, $bNorm);

        if ($k >= 1) {
            return ['c' => 0.0, 'm' => 0.0, 'y' => 0.0, 'k' => 100.0];
        }

        $c = ((1 - $rNorm - $k) / (1 - $k)) * 100;
        $m = ((1 - $gNorm - $k) / (1 - $k)) * 100;
        $y = ((1 - $bNorm - $k) / (1 - $k)) * 100;

        return [
            'c' => round($c, 3),
            'm' => round($m, 3),
            'y' => round($y, 3),
            'k' => round($k * 100, 3),
        ];
    }

    /**
     * @param  array<string, int>  $buckets
     * @return list<array{hex: string, percent: float, rgb: array{r: int, g: int, b: int}}>
     */
    protected function detectedColoursFromBuckets(array $buckets, int $sampled, int $bucketDivisor): array
    {
        arsort($buckets);

        $minPercent = (float) config('printing_intelligence.dominant_colours_min_percent', 0.1);
        $maxCount = (int) config('printing_intelligence.dominant_colours_max_count', 0);
        $colours = [];

        foreach ($buckets as $key => $count) {
            $percent = round(($count / max(1, $sampled)) * 100, 2);

            if ($percent < $minPercent) {
                continue;
            }

            [$ri, $gi, $bi] = array_map('intval', explode('-', $key));
            $midpoint = (int) round($bucketDivisor / 2);
            $r = min(255, $ri * $bucketDivisor + $midpoint);
            $g = min(255, $gi * $bucketDivisor + $midpoint);
            $b = min(255, $bi * $bucketDivisor + $midpoint);
            $colours[] = [
                'hex' => sprintf('#%02X%02X%02X', $r, $g, $b),
                'percent' => $percent,
                'rgb' => ['r' => $r, 'g' => $g, 'b' => $b],
            ];

            if ($maxCount > 0 && count($colours) >= $maxCount) {
                break;
            }
        }

        return $colours;
    }

    /**
     * @return array{status: string, pages: list<array<string, mixed>>, aggregate: array<string, mixed>, warnings: list<string>, raw: array<string, mixed>}
     */
    protected function unsupported(string $message): array
    {
        return [
            'status' => 'unsupported',
            'pages' => [],
            'aggregate' => [],
            'warnings' => [$message],
            'raw' => ['error' => $message],
        ];
    }

    /**
     * @return array{status: string, pages: list<array<string, mixed>>, aggregate: array<string, mixed>, warnings: list<string>, raw: array<string, mixed>}
     */
    protected function failed(string $message): array
    {
        return [
            'status' => 'failed',
            'pages' => [],
            'aggregate' => [],
            'warnings' => [$message],
            'raw' => ['error' => $message],
        ];
    }
}

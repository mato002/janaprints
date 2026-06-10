<?php

namespace App\Services\PrintingIntelligence;

use App\Support\PrintingIntelligence\CoverageClassifier;
use Symfony\Component\Process\Process;

class PdfColourAnalyzer
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
    public function analyze(string $absolutePath): array
    {
        if (! config('printing_intelligence.ghostscript_enabled', true) || ! $this->ghostscriptAvailable()) {
            return [
                'status' => 'manual_review',
                'pages' => [],
                'aggregate' => [],
                'warnings' => [
                    __('Ghostscript not available; PDF ink coverage requires server prepress tool.'),
                ],
                'raw' => [
                    'analyzer' => 'pdf',
                    'ghostscript_available' => false,
                    'manual_pdf_review' => true,
                ],
            ];
        }

        $binary = (string) config('printing_intelligence.ghostscript_binary', 'gs');
        $process = new Process([$binary, '-o', '-', '-sDEVICE=inkcov', $absolutePath]);
        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful()) {
            return [
                'status' => 'manual_review',
                'pages' => [],
                'aggregate' => [],
                'warnings' => [
                    __('Ghostscript inkcov did not return usable output.'),
                    trim($process->getErrorOutput()) ?: __('Unknown Ghostscript error.'),
                ],
                'raw' => [
                    'analyzer' => 'pdf',
                    'stderr' => $process->getErrorOutput(),
                    'manual_pdf_review' => true,
                ],
            ];
        }

        $pages = $this->parseInkcovOutput($process->getOutput());

        if ($pages === []) {
            return [
                'status' => 'manual_review',
                'pages' => [],
                'aggregate' => [],
                'warnings' => [__('Unable to parse Ghostscript inkcov output.')],
                'raw' => [
                    'analyzer' => 'pdf',
                    'stdout' => $process->getOutput(),
                    'manual_pdf_review' => true,
                ],
            ];
        }

        $aggregate = $this->aggregatePages($pages);
        $classification = $this->classifier->classify(array_merge($aggregate, [
            'manual_pdf_review' => false,
        ]));

        $warnings = $classification['warnings'];
        if ($aggregate['cmyk_coverage_percent'] > (float) config('printing_intelligence.heavy_coverage_warning_percent', 80)) {
            $warnings[] = __('high_ink_coverage');
        }

        return [
            'status' => $warnings === [] ? 'completed' : 'manual_review',
            'pages' => $pages,
            'aggregate' => array_merge($aggregate, [
                'coverage_class' => $classification['coverage_class']->value,
                'heavy_coverage_score' => $classification['heavy_coverage_score'],
            ]),
            'warnings' => array_values(array_unique($warnings)),
            'raw' => [
                'analyzer' => 'pdf',
                'ghostscript_binary' => $binary,
                'page_count' => count($pages),
            ],
        ];
    }

    /**
     * Parse Ghostscript inkcov output for unit tests and runtime.
     *
     * @return list<array<string, mixed>>
     */
    public function parseInkcovOutput(string $output): array
    {
        $pages = [];

        foreach (explode("\n", $output) as $line) {
            $line = trim($line);
            if ($line === '' || ! preg_match('/Page\s+(\d+)\s+([\d.]+)\s+([\d.]+)\s+([\d.]+)\s+([\d.]+)/i', $line, $matches)) {
                continue;
            }

            $cyan = round((float) $matches[2] * 100, 3);
            $magenta = round((float) $matches[3] * 100, 3);
            $yellow = round((float) $matches[4] * 100, 3);
            $black = round((float) $matches[5] * 100, 3);
            $cmykCoverage = round(($cyan + $magenta + $yellow + $black) / 4, 3);
            $rgbCoverage = $cmykCoverage;

            $metrics = [
                'page_number' => (int) $matches[1],
                'cyan_coverage_percent' => $cyan,
                'magenta_coverage_percent' => $magenta,
                'yellow_coverage_percent' => $yellow,
                'black_coverage_percent' => $black,
                'cmyk_coverage_percent' => $cmykCoverage,
                'rgb_coverage_percent' => $rgbCoverage,
                'white_area_percent' => round(max(0, 100 - $cmykCoverage), 3),
                'transparent_area_percent' => 0.0,
                'average_ink_density_percent' => $cmykCoverage,
            ];

            $classification = $this->classifier->classify($metrics);

            $pages[] = array_merge($metrics, [
                'dominant_colours' => [],
                'coverage_class' => $classification['coverage_class']->value,
                'colour_analysis_raw' => ['source_line' => $line],
            ]);
        }

        return $pages;
    }

    public function ghostscriptAvailable(): bool
    {
        $binary = (string) config('printing_intelligence.ghostscript_binary', 'gs');
        $process = new Process([$binary, '--version']);
        $process->run();

        return $process->isSuccessful();
    }

    /**
     * @param  list<array<string, mixed>>  $pages
     * @return array<string, mixed>
     */
    protected function aggregatePages(array $pages): array
    {
        $count = count($pages);
        if ($count === 0) {
            return [];
        }

        $keys = [
            'rgb_coverage_percent', 'cmyk_coverage_percent', 'cyan_coverage_percent',
            'magenta_coverage_percent', 'yellow_coverage_percent', 'black_coverage_percent',
            'white_area_percent', 'transparent_area_percent', 'average_ink_density_percent',
        ];

        $aggregate = [];
        foreach ($keys as $key) {
            $aggregate[$key] = round(collect($pages)->avg($key) ?? 0, 3);
        }

        return $aggregate;
    }
}

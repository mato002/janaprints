<?php

namespace App\Support\Export;

use Symfony\Component\HttpFoundation\StreamedResponse;

class TabularExportWriter
{
    public function __construct(
        protected PdfExportService $pdfExports,
    ) {}
    /**
     * @param  list<string>  $headers
     * @param  iterable<int, list<string|float|int|null>>  $rows
     */
    public function download(
        string $format,
        string $basename,
        array $headers,
        iterable $rows,
        ?string $title = null,
        ?string $subtitle = null,
    ): StreamedResponse {
        return match ($format) {
            'excel' => $this->streamExcel($basename, $headers, $rows),
            'pdf' => $this->streamPdf($basename, $headers, $rows, $title, $subtitle),
            default => $this->streamCsv($basename, $headers, $rows),
        };
    }

    /**
     * @param  list<string>  $headers
     * @param  iterable<int, list<string|float|int|null>>  $rows
     */
    public function streamCsv(string $basename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, "{$basename}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  list<string>  $headers
     * @param  iterable<int, list<string|float|int|null>>  $rows
     */
    public function streamExcel(string $basename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            echo "\xEF\xBB\xBF";
            echo '<table border="1"><thead><tr>';

            foreach ($headers as $header) {
                echo '<th>'.e($header).'</th>';
            }

            echo '</tr></thead><tbody>';

            foreach ($rows as $row) {
                echo '<tr>';
                foreach ($row as $cell) {
                    echo '<td>'.e((string) $cell).'</td>';
                }
                echo '</tr>';
            }

            echo '</tbody></table>';
        }, "{$basename}.xls", ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8']);
    }

    /**
     * @param  list<string>  $headers
     * @param  iterable<int, list<string|float|int|null>>  $rows
     */
    public function streamPdf(
        string $basename,
        array $headers,
        iterable $rows,
        ?string $title = null,
        ?string $subtitle = null,
    ): StreamedResponse {
        $materializedRows = [];

        foreach ($rows as $row) {
            $materializedRows[] = $row;
        }

        $orientation = count($headers) > 5 ? 'landscape' : 'portrait';

        return $this->pdfExports->downloadView(
            $basename,
            'exports.tabular-pdf',
            [
                'title' => $title ?? __('Export'),
                'subtitle' => $subtitle,
                'headers' => $headers,
                'rows' => $materializedRows,
                'generatedAt' => now(),
                ...$this->pdfExports->brandingViewData(),
            ],
            $orientation,
        );
    }

    /**
     * Export variable-width rows without a fixed header row.
     *
     * @param  list<list<string|float|int|null>>  $rows
     */
    public function downloadRawRows(
        string $format,
        string $basename,
        array $rows,
        ?string $title = null,
        ?string $subtitle = null,
    ): StreamedResponse {
        return match ($format) {
            'excel' => $this->streamExcelRaw($basename, $rows),
            'pdf' => $this->streamPdfRaw($basename, $rows, $title, $subtitle),
            default => $this->streamCsvRaw($basename, $rows),
        };
    }

    /**
     * @param  list<list<string|float|int|null>>  $rows
     */
    protected function streamCsvRaw(string $basename, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, "{$basename}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  list<list<string|float|int|null>>  $rows
     */
    protected function streamExcelRaw(string $basename, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows) {
            echo "\xEF\xBB\xBF";
            echo '<table border="1"><tbody>';

            foreach ($rows as $row) {
                echo '<tr>';
                foreach ($row as $cell) {
                    echo '<td>'.e((string) $cell).'</td>';
                }
                echo '</tr>';
            }

            echo '</tbody></table>';
        }, "{$basename}.xls", ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8']);
    }

    /**
     * @param  list<list<string|float|int|null>>  $rows
     */
    protected function streamPdfRaw(
        string $basename,
        array $rows,
        ?string $title = null,
        ?string $subtitle = null,
    ): StreamedResponse {
        $orientation = count($rows) > 0 && count($rows[0]) > 5 ? 'landscape' : 'portrait';

        return $this->pdfExports->downloadView(
            $basename,
            'exports.tabular-pdf',
            [
                'title' => $title ?? __('Export'),
                'subtitle' => $subtitle,
                'headers' => [],
                'rows' => $rows,
                'generatedAt' => now(),
                ...$this->pdfExports->brandingViewData(),
            ],
            $orientation,
        );
    }
}

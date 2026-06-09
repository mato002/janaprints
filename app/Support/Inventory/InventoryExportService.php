<?php

namespace App\Support\Inventory;

use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryExportService
{
    /**
     * @param  list<string>  $headers
     * @param  iterable<int, list<string|float|int|null>>  $rows
     */
    public function streamCsv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, "{$filename}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  list<string>  $headers
     * @param  iterable<int, list<string|float|int|null>>  $rows
     */
    public function streamExcel(string $filename, array $headers, iterable $rows): StreamedResponse
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
        }, "{$filename}.xls", ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8']);
    }

    public function streamHtmlDocument(string $filename, string $html): StreamedResponse
    {
        return response()->streamDownload(
            fn () => print($html),
            "{$filename}.html",
            ['Content-Type' => 'text/html; charset=UTF-8'],
        );
    }
}

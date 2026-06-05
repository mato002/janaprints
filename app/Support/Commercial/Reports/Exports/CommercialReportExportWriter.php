<?php

namespace App\Support\Commercial\Reports\Exports;

use App\Models\CommercialReportExport;
use Generator;
use Illuminate\Support\Facades\Storage;

class CommercialReportExportWriter
{
    /**
     * @param  list<string>  $columns
     * @param  Generator<int, array<string, string>>  $rows
     * @return array{path: string, filename: string, mime_type: string, row_count: int}
     */
    public function write(
        CommercialReportExport $export,
        array $columns,
        Generator $rows,
        string $title,
        string $subtitle,
    ): array {
        $extension = CommercialReportExport::extensionForFormat($export->format);
        $basename = "{$export->module}-report-".now()->format('Ymd-His')."-{$export->user_id}";
        $directory = "exports/commercial/{$export->module}/{$export->company_id}";
        $path = "{$directory}/{$basename}.{$extension}";
        $filename = "{$basename}.{$extension}";

        $handle = fopen('php://temp', 'r+');
        $rowCount = 0;

        if ($export->format === 'pdf') {
            $this->writeHtmlOpen($handle, $title, $subtitle, $columns);
            foreach ($rows as $row) {
                $this->writeHtmlRow($handle, $row);
                $rowCount++;
            }
            $this->writeHtmlClose($handle);
        } elseif ($export->format === 'excel') {
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $columns, "\t");
            foreach ($rows as $row) {
                fputcsv($handle, array_values($row), "\t");
                $rowCount++;
            }
        } else {
            fputcsv($handle, $columns);
            foreach ($rows as $row) {
                fputcsv($handle, array_values($row));
                $rowCount++;
            }
        }

        rewind($handle);
        Storage::disk('local')->writeStream($path, $handle);
        fclose($handle);

        return [
            'path' => $path,
            'filename' => $filename,
            'mime_type' => CommercialReportExport::mimeTypeForFormat($export->format),
            'row_count' => $rowCount,
        ];
    }

    /**
     * @param  resource  $handle
     * @param  list<string>  $columns
     */
    protected function writeHtmlOpen($handle, string $title, string $subtitle, array $columns): void
    {
        $header = collect($columns)->map(fn ($col) => '<th>'.e($col).'</th>')->implode('');

        fwrite($handle, <<<HTML
        <!DOCTYPE html>
        <html><head><meta charset="utf-8"><title>{$title}</title>
        <style>body{font-family:Inter,Arial,sans-serif;padding:24px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #e5e7eb;padding:8px;text-align:left}th{background:#f8f9fc}</style>
        </head><body>
        <h1>{$title}</h1>
        <p>{$subtitle}</p>
        <table><thead><tr>{$header}</tr></thead><tbody>
        HTML);
    }

    /**
     * @param  resource  $handle
     * @param  array<string, string>  $row
     */
    protected function writeHtmlRow($handle, array $row): void
    {
        $cells = collect($row)->map(fn ($value) => '<td>'.e((string) $value).'</td>')->implode('');
        fwrite($handle, "<tr>{$cells}</tr>");
    }

    /** @param  resource  $handle */
    protected function writeHtmlClose($handle): void
    {
        fwrite($handle, '</tbody></table></body></html>');
    }
}

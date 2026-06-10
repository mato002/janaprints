<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Support\Export\TabularExportWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait ExportsTabularIndex
{
    /**
     * @param  list<string>  $headers
     * @param  iterable<int, list<string|float|int|null>>  $rows
     */
    protected function downloadTabularExport(
        TabularExportWriter $writer,
        string $format,
        string $basename,
        array $headers,
        iterable $rows,
        ?string $title = null,
    ): StreamedResponse {
        if (! in_array($format, ['csv', 'excel', 'pdf'], true)) {
            abort(404);
        }

        return $writer->download(
            $format,
            $basename.'-'.now()->format('Y-m-d'),
            $headers,
            $rows,
            $title ?? $basename,
        );
    }
}

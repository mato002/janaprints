<?php

namespace App\Http\Controllers\Admin\Export;

use App\Http\Controllers\Controller;
use App\Support\Export\AccountingListingExporter;
use App\Support\Export\TabularExportWriter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountingListingExportController extends Controller
{
    public function download(
        Request $request,
        string $listing,
        string $format,
        AccountingListingExporter $exporter,
        TabularExportWriter $writer,
    ): StreamedResponse {
        return $exporter->download($listing, $format, $writer, $request);
    }
}

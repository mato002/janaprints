<?php

namespace App\Http\Controllers\Admin\Export;

use App\Http\Controllers\Controller;
use App\Support\Export\SupplyChainListingExporter;
use App\Support\Export\TabularExportWriter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupplyChainListingExportController extends Controller
{
    public function inventory(
        Request $request,
        string $listing,
        string $format,
        SupplyChainListingExporter $exporter,
        TabularExportWriter $writer,
    ): StreamedResponse {
        return $exporter->downloadInventory($listing, $format, $writer, $request);
    }

    public function procurement(
        Request $request,
        string $listing,
        string $format,
        SupplyChainListingExporter $exporter,
        TabularExportWriter $writer,
    ): StreamedResponse {
        return $exporter->downloadProcurement($listing, $format, $writer, $request);
    }
}

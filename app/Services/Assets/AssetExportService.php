<?php

namespace App\Services\Assets;

use App\Models\Assets\FixedAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetExportService
{
    public function __construct(
        protected AssetRegisterIndexService $index,
    ) {}

    public function export(Request $request, string $format): StreamedResponse
    {
        $assets = $this->index->exportQuery($request);
        $filename = 'assets-'.now()->format('Y-m-d-His').($format === 'excel' ? '.xls' : '.csv');
        $mime = $format === 'excel'
            ? 'application/vnd.ms-excel'
            : 'text/csv';

        return response()->streamDownload(function () use ($assets, $format) {
            $handle = fopen('php://output', 'w');

            if ($format === 'excel') {
                fwrite($handle, "\xEF\xBB\xBF");
            }

            fputcsv($handle, [
                __('Asset Number'),
                __('Asset Name'),
                __('Category'),
                __('Branch'),
                __('Assigned To'),
                __('Acquisition Cost'),
                __('Book Value'),
                __('Status'),
                __('Created Date'),
            ]);

            foreach ($assets as $asset) {
                /** @var FixedAsset $asset */
                fputcsv($handle, [
                    $asset->asset_number,
                    $asset->asset_name,
                    $asset->category?->name,
                    $asset->branch?->name,
                    $asset->assignedUser?->name,
                    number_format((float) $asset->acquisition_cost, 2, '.', ''),
                    number_format($asset->netBookValue(), 2, '.', ''),
                    $asset->status->label(),
                    $asset->created_at?->format('Y-m-d'),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => $mime]);
    }
}

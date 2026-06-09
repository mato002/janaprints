<?php

namespace App\Services\Assets;

use App\Models\Assets\FixedAsset;
use App\Support\Export\TabularExportWriter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetExportService
{
    public function __construct(
        protected AssetRegisterIndexService $index,
        protected TabularExportWriter $writer,
    ) {}

    public function export(Request $request, string $format): StreamedResponse
    {
        $assets = $this->index->exportQuery($request);
        $headers = [
            __('Asset Number'),
            __('Asset Name'),
            __('Category'),
            __('Branch'),
            __('Assigned To'),
            __('Acquisition Cost'),
            __('Book Value'),
            __('Status'),
            __('Created Date'),
        ];

        $rows = $assets->map(function (FixedAsset $asset) {
            return [
                $asset->asset_number,
                $asset->asset_name,
                $asset->category?->name,
                $asset->branch?->name,
                $asset->assignedUser?->name,
                number_format((float) $asset->acquisition_cost, 2, '.', ''),
                number_format($asset->netBookValue(), 2, '.', ''),
                $asset->status->label(),
                $asset->created_at?->format('Y-m-d'),
            ];
        });

        return $this->writer->download(
            $format,
            'assets-'.now()->format('Y-m-d-His'),
            $headers,
            $rows,
            __('Asset Register'),
        );
    }
}

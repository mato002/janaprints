<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\Assets\FixedAsset;
use App\Services\Assets\AssetExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetExportController extends Controller
{
    public function __construct(
        protected AssetExportService $export,
    ) {}

    public function __invoke(Request $request, string $format): StreamedResponse
    {
        $this->authorize('export', FixedAsset::class);

        return $this->export->export($request, $format);
    }
}

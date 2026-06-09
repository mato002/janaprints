<?php

namespace App\Http\Controllers;

use App\Support\Branding\BrandingAssets;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BrandingAssetController extends Controller
{
    public function show(string $path): StreamedResponse
    {
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        if ($path === '' || str_contains($path, '..') || ! str_starts_with($path, 'branding/')) {
            abort(404);
        }

        $disk = Storage::disk(BrandingAssets::DISK);

        if (! $disk->exists($path)) {
            abort(404);
        }

        return $disk->response($path, null, [
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}

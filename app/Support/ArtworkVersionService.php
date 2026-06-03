<?php

namespace App\Support;

use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ArtworkVersionService
{
    public static function store(ArtworkRequest $request, UploadedFile $file, ?string $notes = null): ArtworkVersion
    {
        $versionNumber = $request->current_version + 1;
        $directory = "artwork/{$request->company_id}/{$request->id}/versions";
        $storedPath = $file->store($directory, 'local');

        $version = ArtworkVersion::query()->create([
            'artwork_request_id' => $request->id,
            'version_number' => $versionNumber,
            'file_path' => $storedPath,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
            'notes' => $notes,
        ]);

        $request->update(['current_version' => $versionNumber]);

        return $version;
    }

    public static function deleteFile(ArtworkVersion $version): void
    {
        if ($version->file_path && Storage::disk('local')->exists($version->file_path)) {
            Storage::disk('local')->delete($version->file_path);
        }
    }
}

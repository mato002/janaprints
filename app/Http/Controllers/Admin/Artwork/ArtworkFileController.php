<?php

namespace App\Http\Controllers\Admin\Artwork;

use App\Http\Controllers\Controller;
use App\Models\Artwork\ArtworkFile;
use App\Models\Artwork\ArtworkRequest;
use App\Support\ArtworkFileHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArtworkFileController extends Controller
{
    public function store(Request $request, ArtworkRequest $artworkRequest): RedirectResponse
    {
        $this->authorize('update', $artworkRequest);

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:51200', ArtworkFileHelper::mimeRule()],
        ]);

        $uploaded = $validated['file'];
        $extension = strtolower($uploaded->getClientOriginalExtension());
        $fileType = ArtworkFileHelper::typeFromExtension($extension);

        if (! $fileType) {
            return back()->withErrors(['file' => __('Unsupported file type.')]);
        }

        $path = $uploaded->store(
            "artwork/{$artworkRequest->company_id}/{$artworkRequest->id}/files",
            'local',
        );

        ArtworkFile::query()->create([
            'company_id' => $artworkRequest->company_id,
            'branch_id' => $artworkRequest->branch_id,
            'artwork_request_id' => $artworkRequest->id,
            'uploaded_by' => auth()->id(),
            'file_type' => $fileType,
            'original_name' => $uploaded->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $uploaded->getMimeType(),
            'size' => $uploaded->getSize(),
        ]);

        return back()->with('status', __('Reference file uploaded.'));
    }

    public function destroy(ArtworkRequest $artworkRequest, ArtworkFile $file): RedirectResponse
    {
        $this->authorize('update', $artworkRequest);

        if ($file->artwork_request_id !== $artworkRequest->id) {
            abort(404);
        }

        if (Storage::disk('local')->exists($file->path)) {
            Storage::disk('local')->delete($file->path);
        }

        $file->delete();

        return back()->with('status', __('Reference file removed.'));
    }
}

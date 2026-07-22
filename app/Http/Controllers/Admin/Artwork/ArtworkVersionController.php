<?php

namespace App\Http\Controllers\Admin\Artwork;

use App\Http\Controllers\Controller;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use App\Support\ArtworkFileHelper;
use App\Support\ArtworkVersionService;
use App\Support\Artwork\ReturnsToDesignerDesk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ArtworkVersionController extends Controller
{
    use ReturnsToDesignerDesk;

    public function store(Request $request, ArtworkRequest $artworkRequest): RedirectResponse
    {
        $this->authorize('create', [ArtworkVersion::class, $artworkRequest]);

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:51200', ArtworkFileHelper::mimeRule()],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        ArtworkVersionService::store($artworkRequest, $validated['file'], $validated['notes'] ?? null);

        if ($this->wantsDesignerDeskReturn($request)) {
            return redirect()->to($this->designerDeskUrl())
                ->with('status', __('New artwork version uploaded.'));
        }

        return back()->with('status', __('New artwork version uploaded.'));
    }

    public function preview(ArtworkRequest $artworkRequest, ArtworkVersion $version): BinaryFileResponse
    {
        abort_unless((int) $version->artwork_request_id === (int) $artworkRequest->id, 404);

        $this->authorize('view', $version);

        abort_unless($version->file_path && Storage::disk('local')->exists($version->file_path), 404);

        return response()->file(Storage::disk('local')->path($version->file_path), [
            'Content-Type' => $version->mime_type ?? 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.addslashes($version->original_name).'"',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin\Artwork;

use App\Http\Controllers\Controller;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use App\Support\ArtworkFileHelper;
use App\Support\ArtworkVersionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ArtworkVersionController extends Controller
{
    public function store(Request $request, ArtworkRequest $artworkRequest): RedirectResponse
    {
        $this->authorize('create', [ArtworkVersion::class, $artworkRequest]);

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:51200', ArtworkFileHelper::mimeRule()],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        ArtworkVersionService::store($artworkRequest, $validated['file'], $validated['notes'] ?? null);

        return back()->with('status', __('New artwork version uploaded.'));
    }
}

<?php

namespace App\Http\Controllers\Admin\Artwork;

use App\Enums\ArtworkCommentType;
use App\Http\Controllers\Controller;
use App\Models\Artwork\ArtworkComment;
use App\Models\Artwork\ArtworkRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ArtworkCommentController extends Controller
{
    public function store(Request $request, ArtworkRequest $artworkRequest): RedirectResponse
    {
        $this->authorize('view', $artworkRequest);

        $validated = $request->validate([
            'comment_type' => ['required', Rule::enum(ArtworkCommentType::class)],
            'comment' => ['required', 'string', 'max:5000'],
        ]);

        ArtworkComment::query()->create([
            'company_id' => $artworkRequest->company_id,
            'branch_id' => $artworkRequest->branch_id,
            'artwork_request_id' => $artworkRequest->id,
            'user_id' => auth()->id(),
            'comment_type' => $validated['comment_type'],
            'comment' => $validated['comment'],
        ]);

        return back()->with('status', __('Comment added.'));
    }
}

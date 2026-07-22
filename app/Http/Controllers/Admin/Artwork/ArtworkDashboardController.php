<?php

namespace App\Http\Controllers\Admin\Artwork;

use App\Enums\ArtworkRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\Artwork\ArtworkRequest;
use App\Support\Artwork\DesignerOperatorMode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArtworkDashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $this->authorize('viewAny', ArtworkRequest::class);

        if (DesignerOperatorMode::enabledFor($request->user())) {
            return redirect()->to(DesignerOperatorMode::homeUrl());
        }

        $base = ArtworkRequest::query()->forTenant();

        $stats = [
            'open' => (clone $base)->where('status', ArtworkRequestStatus::Requested)->count(),
            'in_design' => (clone $base)->where('status', ArtworkRequestStatus::InDesign)->count(),
            'awaiting_approval' => (clone $base)->where('status', ArtworkRequestStatus::Submitted)->count(),
            'approved' => (clone $base)->where('status', ArtworkRequestStatus::Approved)->count(),
            'revision_requests' => (clone $base)->where('status', ArtworkRequestStatus::RevisionRequested)->count(),
        ];

        return view('admin.artwork.dashboard', compact('stats'));
    }
}

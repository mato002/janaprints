<?php

namespace App\Http\Controllers\Admin\Artwork;

use App\Enums\ArtworkRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\Artwork\ArtworkRequest;
use Illuminate\View\View;

class ArtworkDashboardController extends Controller
{
    public function __invoke(): View
    {
        $this->authorize('viewAny', ArtworkRequest::class);

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

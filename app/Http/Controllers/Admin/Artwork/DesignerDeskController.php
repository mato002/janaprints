<?php

namespace App\Http\Controllers\Admin\Artwork;

use App\Enums\ArtworkRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\Artwork\ArtworkRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DesignerDeskController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ArtworkRequest::class);

        $designerId = $request->user()->id;

        $statusCounts = ArtworkRequest::forTenant()
            ->where('assigned_designer_id', $designerId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $summary = [
            'in_design' => $statusCounts[ArtworkRequestStatus::InDesign->value] ?? 0,
            'submitted' => $statusCounts[ArtworkRequestStatus::Submitted->value] ?? 0,
            'revision_requested' => $statusCounts[ArtworkRequestStatus::RevisionRequested->value] ?? 0,
            'approved' => $statusCounts[ArtworkRequestStatus::Approved->value] ?? 0,
        ];

        $requests = ArtworkRequest::forTenant()
            ->where('assigned_designer_id', $designerId)
            ->with(['customer', 'versions'])
            ->latest('updated_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.artwork.desk.index', [
            'summary' => $summary,
            'requests' => $requests,
            'operatorMode' => $request->user()?->prefersDesignerOperatorMode() ?? false,
        ]);
    }
}

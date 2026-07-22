<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\Assets\AssetCapitalizationCandidate;
use App\Services\Assets\AcquisitionsWorkspaceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcquisitionsWorkspaceController extends Controller
{
    public function __invoke(Request $request, AcquisitionsWorkspaceService $workspace): View
    {
        $user = $request->user();
        abort_unless(
            $user?->can('assets.acquisition.view') || $user?->can('assets.reconciliation.view'),
            403,
        );

        if ($user?->can('assets.acquisition.view')) {
            $this->authorize('viewAny', AssetCapitalizationCandidate::class);
        }

        return view('admin.assets.acquisitions.hub', $workspace->build($request));
    }
}

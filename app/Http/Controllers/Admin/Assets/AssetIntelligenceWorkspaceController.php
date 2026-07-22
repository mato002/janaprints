<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Services\Assets\AssetIntelligenceWorkspaceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetIntelligenceWorkspaceController extends Controller
{
    public function __invoke(Request $request, AssetIntelligenceWorkspaceService $workspace): View
    {
        abort_unless($request->user()?->can('assets.analytics.view'), 403);

        return view('admin.assets.intelligence.hub', $workspace->build($request));
    }
}

<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\Assets\AssetHandover;
use App\Services\Assets\CustodyWorkspaceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustodyWorkspaceController extends Controller
{
    public function __invoke(Request $request, CustodyWorkspaceService $workspace): View
    {
        $this->authorize('viewAny', AssetHandover::class);

        return view('admin.assets.custody.hub', $workspace->build($request));
    }
}

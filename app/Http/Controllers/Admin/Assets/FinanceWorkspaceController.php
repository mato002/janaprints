<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\Assets\DepreciationRun;
use App\Services\Assets\FinanceWorkspaceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceWorkspaceController extends Controller
{
    public function __invoke(Request $request, FinanceWorkspaceService $workspace): View
    {
        $this->authorize('viewAny', DepreciationRun::class);

        return view('admin.assets.finance.hub', $workspace->build($request));
    }
}

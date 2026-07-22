<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\Assets\DepreciationRun;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AssetFinanceReportController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', DepreciationRun::class);

        return redirect()->route('admin.assets.finance.dashboard', array_merge(
            $request->query(),
            ['tab' => 'reports'],
        ));
    }
}

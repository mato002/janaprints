<?php

namespace App\Http\Controllers\Admin\Production;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Production\WorkCenter;
use Illuminate\View\View;

class WorkCenterController extends Controller
{
    use ScopesToTenant;

    public function index(): View
    {
        $this->authorize('viewAny', \App\Models\Production\ProductionJobCard::class);

        $workCenters = $this->scopeToTenant(
            WorkCenter::query()->orderBy('name')
        )->paginate(config('platform.pagination.default', 15));

        $stages = $this->scopeToTenant(
            \App\Models\Production\ProductionStage::query()->orderBy('sort_order')
        )->get();

        return view('admin.production.work-centers.index', compact('workCenters', 'stages'));
    }
}

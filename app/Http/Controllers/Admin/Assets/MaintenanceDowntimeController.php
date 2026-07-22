<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Enums\DowntimeImpactLevel;
use App\Http\Controllers\Controller;
use App\Models\Assets\AssetDowntimeRecord;
use App\Models\Assets\FixedAsset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Services\Assets\MaintenanceDowntimeService;

class MaintenanceDowntimeController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', AssetDowntimeRecord::class);

        return redirect()->route('admin.assets.maintenance.dashboard', array_merge(
            $request->query(),
            ['tab' => 'downtime'],
        ));
    }

    public function store(Request $request, MaintenanceDowntimeService $downtime): RedirectResponse
    {
        $this->authorize('create', AssetDowntimeRecord::class);

        $validated = $request->validate([
            'fixed_asset_id' => ['required', 'exists:fixed_assets,id'],
            'start_time' => ['required', 'date'],
            'end_time' => ['nullable', 'date', 'after:start_time'],
            'reason' => ['nullable', 'string'],
            'impact_level' => ['required', Rule::enum(DowntimeImpactLevel::class)],
            'maintenance_work_order_id' => ['nullable', 'exists:maintenance_work_orders,id'],
        ]);

        $downtime->record($validated, (int) tenant()->companyId(), tenant()->branchId());

        return back()->with('status', __('Downtime record saved.'));
    }
}

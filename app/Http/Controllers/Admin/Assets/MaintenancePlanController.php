<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Enums\MaintenanceFrequencyType;
use App\Http\Controllers\Controller;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\MaintenancePlan;
use App\Services\Assets\MaintenancePlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaintenancePlanController extends Controller
{
    public function __construct(
        protected MaintenancePlanService $plans,
    ) {}

    public function index(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', MaintenancePlan::class);

        return redirect()->route('admin.assets.maintenance.dashboard', array_merge(
            $request->query(),
            ['tab' => 'plans'],
        ));
    }

    public function create(): View
    {
        $this->authorize('create', MaintenancePlan::class);

        return view('admin.assets.maintenance.plans.create', [
            'assets' => FixedAsset::query()->forTenant()->notArchived()->orderBy('asset_name')->get(['id', 'asset_name', 'asset_number']),
            'frequencies' => MaintenanceFrequencyType::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', MaintenancePlan::class);

        $validated = $request->validate([
            'fixed_asset_id' => ['required', 'exists:fixed_assets,id'],
            'plan_name' => ['required', 'string', 'max:255'],
            'frequency_type' => ['required', Rule::enum(MaintenanceFrequencyType::class)],
            'frequency_value' => ['required', 'integer', 'min:1'],
            'next_due_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        $this->plans->create($validated, (int) tenant()->companyId(), tenant()->branchId());

        return redirect()
            ->route('admin.assets.maintenance.dashboard', ['tab' => 'plans'])
            ->with('status', __('Maintenance plan created.'));
    }
}

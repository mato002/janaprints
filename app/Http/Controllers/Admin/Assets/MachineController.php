<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Enums\AssetType;
use App\Enums\MachineCapacityUnit;
use App\Enums\ProductionMachineStatus;
use App\Http\Controllers\Controller;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\MachineProfile;
use App\Models\Branch;
use App\Services\Assets\MachineIndexService;
use App\Services\Assets\MachineProfileService;
use App\Services\Assets\MachineShowService;
use App\Services\Assets\MachineStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MachineController extends Controller
{
    public function __construct(
        protected MachineIndexService $index,
        protected MachineShowService $show,
        protected MachineProfileService $profiles,
        protected MachineStatusService $status,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', MachineProfile::class);

        return view('admin.assets.machines.index', $this->index->build($request));
    }

    public function create(): View
    {
        $this->authorize('create', MachineProfile::class);

        $categories = AssetCategory::query()->forTenant()->active()->orderBy('name')->get();

        return view('admin.assets.machines.create', [
            'categories' => $categories,
            'branches' => Branch::query()->where('company_id', tenant()->companyId())->orderBy('name')->get(),
            'defaultCategoryId' => $categories->first(
                fn (AssetCategory $category) => in_array($category->asset_type, [
                    AssetType::Machine,
                    AssetType::Printer,
                    AssetType::Plotter,
                ], true),
            )?->id ?? $categories->first()?->id,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', MachineProfile::class);

        $validated = $request->validate([
            'asset_name' => ['required', 'string', 'max:255'],
            'asset_category_id' => ['required', 'exists:asset_categories,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'acquisition_date' => ['nullable', 'date'],
            'acquisition_cost' => ['nullable', 'numeric', 'min:0'],
            'machine_code' => ['required', 'string', 'max:50'],
            'machine_type' => ['required', 'string', 'max:50'],
            'manufacturer' => ['nullable', 'string', 'max:120'],
            'model' => ['nullable', 'string', 'max:120'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'production_area' => ['nullable', 'string', 'max:120'],
            'installation_date' => ['nullable', 'date'],
            'capacity_unit' => ['nullable', Rule::enum(MachineCapacityUnit::class)],
            'capacity_per_hour' => ['nullable', 'numeric', 'min:0'],
            'capacity_per_shift' => ['nullable', 'numeric', 'min:0'],
            'is_primary_production_machine' => ['nullable', 'boolean'],
            'hourly_capacity' => ['nullable', 'numeric', 'min:0'],
            'daily_capacity' => ['nullable', 'numeric', 'min:0'],
            'shift_capacity' => ['nullable', 'numeric', 'min:0'],
            'monthly_capacity' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $profile = $this->profiles->registerMachine(
            $validated,
            (int) tenant()->companyId(),
            tenant()->branchId(),
            (int) auth()->id(),
        );

        return redirect()
            ->route('admin.assets.machines.show', $profile->asset)
            ->with('status', __('Machine registered.'));
    }

    public function show(FixedAsset $asset): View
    {
        $profile = $asset->machineProfile;
        abort_unless($profile, 404);
        $this->authorize('view', $profile);

        return view('admin.assets.machines.show', $this->show->build($asset));
    }

    public function activate(Request $request, FixedAsset $asset): RedirectResponse
    {
        $this->authorize('create', MachineProfile::class);

        $validated = $request->validate([
            'machine_code' => ['required', 'string', 'max:50'],
            'machine_type' => ['required', 'string', 'max:50'],
            'manufacturer' => ['nullable', 'string', 'max:120'],
            'model' => ['nullable', 'string', 'max:120'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'production_area' => ['nullable', 'string', 'max:120'],
            'installation_date' => ['nullable', 'date'],
            'capacity_unit' => ['nullable', Rule::enum(MachineCapacityUnit::class)],
            'capacity_per_hour' => ['nullable', 'numeric', 'min:0'],
            'capacity_per_shift' => ['nullable', 'numeric', 'min:0'],
            'is_primary_production_machine' => ['nullable', 'boolean'],
            'hourly_capacity' => ['nullable', 'numeric', 'min:0'],
            'daily_capacity' => ['nullable', 'numeric', 'min:0'],
            'shift_capacity' => ['nullable', 'numeric', 'min:0'],
            'monthly_capacity' => ['nullable', 'numeric', 'min:0'],
        ]);

        $this->profiles->createForAsset($asset, $validated, (int) auth()->id());

        return redirect()
            ->route('admin.assets.machines.show', $asset)
            ->with('status', __('Machine profile created.'));
    }

    public function updateStatus(Request $request, FixedAsset $asset): RedirectResponse
    {
        $profile = $asset->machineProfile;
        abort_unless($profile, 404);
        $this->authorize('manage', $profile);

        $validated = $request->validate([
            'production_status' => ['required', Rule::enum(ProductionMachineStatus::class)],
        ]);

        $this->status->changeStatus(
            $profile,
            ProductionMachineStatus::from($validated['production_status']),
            (int) auth()->id(),
        );

        return back()->with('status', __('Machine status updated.'));
    }

    public function updateCapacity(Request $request, FixedAsset $asset): RedirectResponse
    {
        $profile = $asset->machineProfile;
        abort_unless($profile, 404);
        $this->authorize('updateCapacity', $profile);

        $validated = $request->validate([
            'hourly_capacity' => ['nullable', 'numeric', 'min:0'],
            'daily_capacity' => ['nullable', 'numeric', 'min:0'],
            'shift_capacity' => ['nullable', 'numeric', 'min:0'],
            'monthly_capacity' => ['nullable', 'numeric', 'min:0'],
            'capacity_per_hour' => ['nullable', 'numeric', 'min:0'],
            'capacity_per_shift' => ['nullable', 'numeric', 'min:0'],
        ]);

        $this->profiles->updateProfile($profile, $validated, (int) auth()->id());

        return back()->with('status', __('Machine capacity updated.'));
    }

    public function assignWorkCenter(Request $request, FixedAsset $asset): RedirectResponse
    {
        $profile = $asset->machineProfile;
        abort_unless($profile, 404);
        $this->authorize('assign', $profile);

        $validated = $request->validate([
            'work_center_id' => ['nullable', 'exists:work_centers,id'],
        ]);

        $this->profiles->assignWorkCenter(
            $profile,
            isset($validated['work_center_id']) ? (int) $validated['work_center_id'] : null,
            (int) auth()->id(),
        );

        return back()->with('status', __('Work center assignment updated.'));
    }
}

<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Enums\FixedAssetStatus;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\Branch;
use App\Models\User;
use App\Services\Assets\AssetAssignmentService;
use App\Services\Assets\AssetManagementWorkspaceService;
use App\Services\Assets\AssetRegisterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FixedAssetController extends Controller
{
    public function __construct(
        protected AssetManagementWorkspaceService $workspace,
        protected AssetRegisterService $register,
        protected AssetAssignmentService $assignments,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', FixedAsset::class);

        return view('admin.assets.index', $this->workspace->build($request));
    }

    public function create(): View
    {
        $this->authorize('create', FixedAsset::class);

        return view('admin.assets.create', [
            'categories' => AssetCategory::query()->forTenant()->active()->orderBy('name')->get(),
            'branches' => Branch::query()->where('company_id', tenant()->companyId())->orderBy('name')->get(),
            'users' => User::query()->where('company_id', tenant()->companyId())->where('is_active', true)->orderBy('name')->get(),
            'statuses' => FixedAssetStatus::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', FixedAsset::class);

        $validated = $request->validate([
            'asset_category_id' => ['required', 'exists:asset_categories,id'],
            'asset_name' => ['required', 'string', 'max:255'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'manufacturer' => ['nullable', 'string', 'max:120'],
            'model' => ['nullable', 'string', 'max:120'],
            'acquisition_date' => ['required', 'date'],
            'acquisition_cost' => ['required', 'numeric', 'min:0'],
            'residual_value' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string'],
            'assigned_to_user_id' => ['nullable', 'exists:users,id'],
            'assigned_to_branch_id' => ['nullable', 'exists:branches,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $asset = $this->register->create(
            $validated,
            (int) tenant()->companyId(),
            tenant()->branchId(),
            (int) auth()->id(),
        );

        return redirect()->route('admin.assets.show', $asset)->with('status', __('Asset registered.'));
    }

    public function show(FixedAsset $asset): View
    {
        $this->authorize('view', $asset);

        $asset->load([
            'machineProfile',
            'category',
            'branch',
            'assignedUser',
            'assignedBranch',
            'maintenances' => fn ($q) => $q->latest()->limit(10),
            'depreciationEntries' => fn ($q) => $q->latest('period_date')->limit(10),
            'assignedEmployee',
            'assignedDepartment',
            'assignmentHistories.assigner',
            'assignmentHistories.assignedUser',
            'assignmentHistories.assignedBranch',
            'assignmentHistories.assignedEmployee',
            'assignmentHistories.assignedDepartment',
            'custodyTimelineEntries' => fn ($q) => $q->with('user:id,name')->limit(20),
            'conditionHistories' => fn ($q) => $q->with('recorder:id,name')->limit(10),
            'maintenanceTimelineEntries' => fn ($q) => $q->with('user:id,name')->limit(15),
            'maintenanceWorkOrders' => fn ($q) => $q->with('assignee:id,name')->limit(5),
        ]);

        $activityLogs = ActivityLog::query()
            ->where('model_type', FixedAsset::class)
            ->where('model_id', $asset->id)
            ->with('user:id,name')
            ->latest('created_at')
            ->limit(25)
            ->get();

        return view('admin.assets.show', [
            'asset' => $asset,
            'activityLogs' => $activityLogs,
            'branches' => Branch::query()->where('company_id', $asset->company_id)->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->where('company_id', $asset->company_id)->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'statuses' => FixedAssetStatus::cases(),
        ]);
    }

    public function edit(FixedAsset $asset): View
    {
        $this->authorize('update', $asset);

        return view('admin.assets.edit', [
            'asset' => $asset->load('category'),
            'categories' => AssetCategory::query()->forTenant()->active()->orderBy('name')->get(),
            'branches' => Branch::query()->where('company_id', $asset->company_id)->orderBy('name')->get(),
            'users' => User::query()->where('company_id', $asset->company_id)->where('is_active', true)->orderBy('name')->get(),
            'statuses' => FixedAssetStatus::cases(),
        ]);
    }

    public function update(Request $request, FixedAsset $asset): RedirectResponse
    {
        $this->authorize('update', $asset);

        $validated = $request->validate([
            'asset_category_id' => ['required', 'exists:asset_categories,id'],
            'asset_name' => ['required', 'string', 'max:255'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'manufacturer' => ['nullable', 'string', 'max:120'],
            'model' => ['nullable', 'string', 'max:120'],
            'acquisition_date' => ['required', 'date'],
            'acquisition_cost' => ['required', 'numeric', 'min:0'],
            'residual_value' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string'],
            'assigned_to_user_id' => ['nullable', 'exists:users,id'],
            'assigned_to_branch_id' => ['nullable', 'exists:branches,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->register->update($asset, $validated, (int) auth()->id());

        return redirect()->route('admin.assets.show', $asset)->with('status', __('Asset updated.'));
    }

    public function assign(Request $request, FixedAsset $asset): RedirectResponse
    {
        $this->authorize('assign', $asset);

        $validated = $request->validate([
            'assignment_type' => ['required', 'in:user,branch'],
            'assigned_to_user_id' => ['required_if:assignment_type,user', 'nullable', 'exists:users,id'],
            'assigned_to_branch_id' => ['required_if:assignment_type,branch', 'nullable', 'exists:branches,id'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validated['assignment_type'] === 'user') {
            $this->assignments->assignToUser($asset, (int) $validated['assigned_to_user_id'], (int) auth()->id(), $validated['notes'] ?? null);
        } else {
            $this->assignments->assignToBranch($asset, (int) $validated['assigned_to_branch_id'], (int) auth()->id(), $validated['notes'] ?? null);
        }

        return back()->with('status', __('Asset assigned.'));
    }

    public function changeStatus(Request $request, FixedAsset $asset): RedirectResponse
    {
        $this->authorize('manage', $asset);

        $validated = $request->validate([
            'status' => ['required', 'string'],
        ]);

        $this->register->changeStatus($asset, FixedAssetStatus::from($validated['status']), (int) auth()->id());

        return back()->with('status', __('Asset status updated.'));
    }

    public function archive(FixedAsset $asset): RedirectResponse
    {
        $this->authorize('archive', $asset);

        $this->register->archive($asset, (int) auth()->id());

        return redirect()->route('admin.assets.index')->with('status', __('Asset archived.'));
    }
}

<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Enums\AssetHandoverStatus;
use App\Enums\AssetPhysicalCondition;
use App\Http\Controllers\Controller;
use App\Models\Assets\AssetHandover;
use App\Models\Assets\FixedAsset;
use App\Models\Branch;
use App\Models\Employee;
use App\Services\Assets\AssetHandoverService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssetHandoverController extends Controller
{
    public function __construct(
        protected AssetHandoverService $handovers,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', AssetHandover::class);

        $companyId = (int) tenant()->companyId();
        $branchId = tenant()->branchId();

        $handovers = AssetHandover::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->with([
                'asset:id,asset_name,asset_number',
                'fromEmployee:id,first_name,last_name',
                'toEmployee:id,first_name,last_name',
                'fromBranch:id,name',
                'toBranch:id,name',
            ])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest('handover_date')
            ->paginate(20)
            ->withQueryString();

        return view('admin.assets.custody.handovers.index', [
            'handovers' => $handovers,
            'statuses' => AssetHandoverStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', AssetHandover::class);

        $companyId = (int) tenant()->companyId();

        return view('admin.assets.custody.handovers.create', [
            'assets' => FixedAsset::query()->forTenant()->notArchived()->orderBy('asset_name')->get(['id', 'asset_name', 'asset_number']),
            'employees' => Employee::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('first_name')->get(),
            'branches' => Branch::query()->where('company_id', $companyId)->orderBy('name')->get(['id', 'name']),
            'conditions' => AssetPhysicalCondition::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', AssetHandover::class);

        $validated = $request->validate([
            'fixed_asset_id' => ['required', 'exists:fixed_assets,id'],
            'from_employee_id' => ['nullable', 'exists:employees,id'],
            'to_employee_id' => ['nullable', 'exists:employees,id'],
            'from_branch_id' => ['nullable', 'exists:branches,id'],
            'to_branch_id' => ['nullable', 'exists:branches,id'],
            'handover_date' => ['required', 'date'],
            'condition' => ['nullable', Rule::enum(AssetPhysicalCondition::class)],
            'condition_notes' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
        ]);

        $handover = $this->handovers->create(
            $validated,
            (int) tenant()->companyId(),
            tenant()->branchId(),
            (int) auth()->id(),
        );

        return redirect()
            ->route('admin.assets.custody.handovers.show', $handover)
            ->with('status', __('Handover record created.'));
    }

    public function show(AssetHandover $handover): View
    {
        $this->authorize('view', $handover);

        $handover->load([
            'asset.category',
            'fromEmployee',
            'toEmployee',
            'fromBranch',
            'toBranch',
            'approver:id,name',
        ]);

        return view('admin.assets.custody.handovers.show', [
            'handover' => $handover,
        ]);
    }

    public function submit(AssetHandover $handover): RedirectResponse
    {
        $this->authorize('manage', $handover);
        $this->handovers->submit($handover, (int) auth()->id());

        return back()->with('status', __('Handover submitted for acceptance.'));
    }

    public function accept(AssetHandover $handover): RedirectResponse
    {
        $this->authorize('manage', $handover);
        $this->handovers->accept($handover, (int) auth()->id());

        return back()->with('status', __('Handover accepted.'));
    }

    public function reject(AssetHandover $handover): RedirectResponse
    {
        $this->authorize('manage', $handover);
        $this->handovers->reject($handover, (int) auth()->id());

        return back()->with('status', __('Handover rejected.'));
    }
}

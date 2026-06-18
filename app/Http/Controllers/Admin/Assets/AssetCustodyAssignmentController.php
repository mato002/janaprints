<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Enums\AssetAssignmentStatus;
use App\Http\Controllers\Admin\Concerns\HandlesModalFormResponses;
use App\Http\Controllers\Controller;
use App\Models\Assets\AssetHandover;
use App\Models\Assets\AssetAssignmentHistory;
use App\Models\Assets\FixedAsset;
use App\Models\Department;
use App\Models\Employee;
use App\Services\Assets\AssetCustodyAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AssetCustodyAssignmentController extends Controller
{
    use HandlesModalFormResponses;

    public function __construct(
        protected AssetCustodyAssignmentService $assignments,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', AssetHandover::class);

        $companyId = (int) tenant()->companyId();
        $branchId = tenant()->branchId();

        $assignments = AssetAssignmentHistory::query()
            ->whereHas('asset', function ($q) use ($companyId, $branchId) {
                $q->where('company_id', $companyId)
                    ->when($branchId, fn ($b) => $b->where('branch_id', $branchId));
            })
            ->with([
                'asset:id,asset_name,asset_number,branch_id',
                'assignedUser:id,name',
                'assignedBranch:id,name',
                'assignedEmployee:id,first_name,last_name',
                'assignedDepartment:id,name',
                'assigner:id,name',
            ])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest('assigned_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.assets.custody.assignments.index', [
            'assignments' => $assignments,
            'statuses' => AssetAssignmentStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('viewAny', AssetHandover::class);
        abort_unless(auth()->user()?->can('assets.assign'), 403);

        return view('admin.assets.custody.assignments.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse|Response
    {
        $validated = $request->validate([
            'fixed_asset_id' => ['required', 'exists:fixed_assets,id'],
            'assignment_type' => ['required', 'in:employee,department'],
            'assigned_to_employee_id' => ['required_if:assignment_type,employee', 'nullable', 'exists:employees,id'],
            'assigned_to_department_id' => ['required_if:assignment_type,department', 'nullable', 'exists:departments,id'],
            'expected_return_date' => ['nullable', 'date'],
            'assignment_reason' => ['nullable', 'string', 'max:120'],
            'condition' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $asset = FixedAsset::query()->forTenant()->findOrFail($validated['fixed_asset_id']);
        $this->authorize('assign', $asset);

        if ($validated['assignment_type'] === 'employee') {
            $this->assignments->assignToEmployee($asset, $validated, (int) auth()->id());
        } else {
            $this->assignments->assignToDepartment($asset, $validated, (int) auth()->id());
        }

        return $this->modalOrRedirect(
            __('Asset assignment recorded.'),
            redirect()->route('admin.assets.custody.assignments.index'),
        );
    }

    /**
     * @return array{employees: \Illuminate\Support\Collection, departments: \Illuminate\Support\Collection, assets: \Illuminate\Support\Collection}
     */
    protected function formMeta(): array
    {
        $companyId = (int) tenant()->companyId();

        return [
            'employees' => Employee::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('first_name')->get(),
            'departments' => Department::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(),
            'assets' => FixedAsset::query()->forTenant()->notArchived()->orderBy('asset_name')->get(['id', 'asset_name', 'asset_number']),
        ];
    }
}

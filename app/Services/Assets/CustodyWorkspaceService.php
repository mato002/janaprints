<?php

namespace App\Services\Assets;

use App\Enums\AssetAssignmentStatus;
use App\Enums\AssetBranchTransferStatus;
use App\Enums\AssetHandoverStatus;
use App\Models\Assets\AssetAssignmentHistory;
use App\Models\Assets\AssetBranchTransfer;
use App\Models\Assets\AssetHandover;
use App\Models\Assets\AssetReturn;
use Illuminate\Http\Request;

class CustodyWorkspaceService
{
    public function __construct(
        protected AssetCustodyDashboardService $dashboard,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Request $request): array
    {
        $activeTab = $this->resolveTab($request);

        $payload = [
            'activeTab' => $activeTab,
            'tabs' => $this->tabs(),
            'hubUrl' => route('admin.assets.custody.dashboard'),
        ];

        return match ($activeTab) {
            'assignments' => array_merge($payload, $this->assignmentsTab($request)),
            'handovers' => array_merge($payload, $this->handoversTab($request)),
            'returns' => array_merge($payload, $this->returnsTab($request)),
            'transfers' => array_merge($payload, $this->transfersTab($request)),
            default => array_merge($payload, [
                'stats' => $this->dashboard->build(
                    (int) tenant()->companyId(),
                    tenant()->branchId(),
                ),
            ]),
        };
    }

    public function resolveTab(Request $request): string
    {
        $tab = (string) $request->query('tab', 'overview');

        if (in_array($tab, ['custody', 'custody-dashboard'], true)) {
            $tab = 'overview';
        }

        return in_array($tab, array_keys($this->tabs()), true) ? $tab : 'overview';
    }

    /**
     * @return array<string, string>
     */
    public function tabs(): array
    {
        return [
            'overview' => __('Overview'),
            'assignments' => __('Assignments'),
            'handovers' => __('Handovers'),
            'returns' => __('Returns'),
            'transfers' => __('Branch Transfers'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function assignmentsTab(Request $request): array
    {
        $companyId = (int) tenant()->companyId();
        $branchId = tenant()->branchId();

        return [
            'assignments' => AssetAssignmentHistory::query()
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
                ->withQueryString(),
            'statuses' => AssetAssignmentStatus::cases(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function handoversTab(Request $request): array
    {
        $companyId = (int) tenant()->companyId();
        $branchId = tenant()->branchId();

        return [
            'handovers' => AssetHandover::query()
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
                ->withQueryString(),
            'statuses' => AssetHandoverStatus::cases(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function returnsTab(Request $request): array
    {
        $companyId = (int) tenant()->companyId();
        $branchId = tenant()->branchId();

        return [
            'returns' => AssetReturn::query()
                ->where('company_id', $companyId)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->with([
                    'asset:id,asset_name,asset_number',
                    'returnedByEmployee:id,first_name,last_name',
                    'receiver:id,name',
                ])
                ->when($request->filled('condition'), fn ($q) => $q->where('condition', $request->string('condition')))
                ->latest('return_date')
                ->paginate(20)
                ->withQueryString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function transfersTab(Request $request): array
    {
        $companyId = (int) tenant()->companyId();
        $branchId = tenant()->branchId();

        return [
            'transfers' => AssetBranchTransfer::query()
                ->where('company_id', $companyId)
                ->when($branchId, fn ($q) => $q->where(function ($b) use ($branchId) {
                    $b->where('from_branch_id', $branchId)->orWhere('to_branch_id', $branchId);
                }))
                ->with([
                    'asset:id,asset_name,asset_number',
                    'fromBranch:id,name',
                    'toBranch:id,name',
                    'requester:id,name',
                ])
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
                ->latest('requested_at')
                ->paginate(20)
                ->withQueryString(),
            'statuses' => AssetBranchTransferStatus::cases(),
        ];
    }
}

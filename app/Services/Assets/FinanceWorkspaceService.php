<?php

namespace App\Services\Assets;

use App\Enums\DepreciationPostingStatus;
use App\Enums\DepreciationRunStatus;
use App\Models\Assets\AssetDepreciationEntry;
use App\Models\Assets\AssetRegisterReconciliation;
use App\Models\Assets\AssetWriteOff;
use App\Models\Assets\DepreciationRun;
use App\Models\User;
use Illuminate\Http\Request;

class FinanceWorkspaceService
{
    public function __construct(
        protected AssetFinanceDashboardService $dashboard,
        protected AssetFinanceReportService $reports,
        protected AssetReplacementService $replacement,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Request $request): array
    {
        $user = $request->user();
        $activeTab = $this->resolveTab($request, $user);
        $payload = [
            'activeTab' => $activeTab,
            'tabs' => $this->tabs($user),
            'hubUrl' => route('admin.assets.finance.dashboard'),
        ];

        return match ($activeTab) {
            'runs' => array_merge($payload, $this->runsTab($request)),
            'entries' => array_merge($payload, $this->entriesTab($request)),
            'reconciliation' => array_merge($payload, $this->reconciliationTab()),
            'reports' => array_merge($payload, $this->reportsTab($request)),
            'write-offs' => array_merge($payload, $this->writeOffsTab()),
            default => array_merge($payload, [
                'stats' => $this->dashboard->build(
                    (int) tenant()->companyId(),
                    tenant()->branchId(),
                ),
            ]),
        };
    }

    public function resolveTab(Request $request, ?User $user = null): string
    {
        $user ??= $request->user();
        $tab = (string) $request->query('tab', 'overview');

        if (in_array($tab, ['finance', 'finance-dashboard'], true)) {
            $tab = 'overview';
        }

        $allowed = array_keys($this->tabs($user));

        return in_array($tab, $allowed, true) ? $tab : 'overview';
    }

    /**
     * @return array<string, string>
     */
    public function tabs(?User $user): array
    {
        $tabs = [
            'overview' => __('Overview'),
            'runs' => __('Depreciation Runs'),
            'entries' => __('Depreciation Entries'),
        ];

        if ($user?->can('assets.reconciliation.view')) {
            $tabs['reconciliation'] = __('Reconciliation');
        }

        $tabs['reports'] = __('Reports');
        $tabs['write-offs'] = __('Write-Offs');

        return $tabs;
    }

    /**
     * @return array<string, mixed>
     */
    protected function runsTab(Request $request): array
    {
        return [
            'runs' => DepreciationRun::query()
                ->where('company_id', tenant()->companyId())
                ->when(tenant()->branchId(), fn ($q) => $q->where('branch_id', tenant()->branchId()))
                ->with('executor:id,name')
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
                ->latest('run_date')
                ->paginate(20)
                ->withQueryString(),
            'statuses' => DepreciationRunStatus::cases(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function entriesTab(Request $request): array
    {
        return [
            'entries' => AssetDepreciationEntry::query()
                ->whereHas('asset', function ($q) {
                    $q->where('company_id', tenant()->companyId());
                    if (tenant()->branchId()) {
                        $q->where('branch_id', tenant()->branchId());
                    }
                })
                ->with(['asset:id,asset_number,asset_name', 'run:id,run_number', 'journal:id,reference'])
                ->when($request->filled('posting_status'), fn ($q) => $q->where('posting_status', $request->string('posting_status')))
                ->latest('period_date')
                ->paginate(25)
                ->withQueryString(),
            'statuses' => DepreciationPostingStatus::cases(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function reconciliationTab(): array
    {
        return [
            'reconciliations' => AssetRegisterReconciliation::query()
                ->where('company_id', tenant()->companyId())
                ->with('reconciler:id,name')
                ->latest('reconciliation_date')
                ->paginate(20),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function reportsTab(Request $request): array
    {
        $report = $request->string('report', 'register')->toString();
        $companyId = (int) tenant()->companyId();
        $filters = array_filter([
            'branch_id' => tenant()->branchId(),
            'category_id' => $request->integer('category_id') ?: null,
            'from' => $request->string('from')->toString() ?: null,
            'to' => $request->string('to')->toString() ?: null,
        ]);

        $data = match ($report) {
            'valuation' => $this->reports->valuationReport($companyId, $filters),
            'depreciation_schedule' => $this->reports->depreciationSchedule($companyId, $filters),
            'fully_depreciated' => $this->reports->fullyDepreciated($companyId, tenant()->branchId()),
            'near_end_of_life' => $this->reports->nearEndOfLife($companyId, tenant()->branchId()),
            'maintenance' => $this->reports->maintenanceReport($companyId, $filters),
            'custody' => $this->reports->custodyReport($companyId, $filters),
            'warranty_expiry' => $this->reports->warrantyExpiryReport($companyId, $filters),
            'replacement' => $this->replacement->candidates($companyId, tenant()->branchId(), 100),
            default => $this->reports->registerReport($companyId, $filters),
        };

        return [
            'report' => $report,
            'data' => $data,
            'filters' => $filters,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function writeOffsTab(): array
    {
        return [
            'writeOffs' => AssetWriteOff::query()
                ->where('company_id', tenant()->companyId())
                ->with(['asset:id,asset_number,asset_name', 'creator:id,name'])
                ->latest('write_off_date')
                ->paginate(20),
        ];
    }
}

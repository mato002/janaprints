<?php

namespace App\Services\Assets;

use App\Models\Assets\AssetCapitalizationCandidate;
use App\Models\Assets\AssetCapitalizationReconciliation;
use App\Models\Assets\AssetWarranty;
use App\Models\User;
use Illuminate\Http\Request;

class AcquisitionsWorkspaceService
{
    public function __construct(
        protected AssetAcquisitionDashboardService $dashboard,
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
            'hubUrl' => route('admin.assets.acquisitions.dashboard'),
        ];

        return match ($activeTab) {
            'queue' => array_merge($payload, $this->queueTab($request)),
            'warranties' => array_merge($payload, $this->warrantiesTab($request)),
            'reconciliation' => array_merge($payload, $this->reconciliationTab()),
            default => array_merge($payload, [
                'stats' => $this->dashboard->build(
                    (int) tenant()->companyId(),
                    tenant()->branchId(),
                ),
            ]),
        };
    }

    public function tabs(?User $user): array
    {
        $tabs = [];

        if ($user?->can('assets.acquisition.view')) {
            $tabs['overview'] = __('Overview');
            $tabs['queue'] = __('Capitalization Queue');
            $tabs['warranties'] = __('Warranty Center');
        }

        if ($user?->can('assets.reconciliation.view')) {
            $tabs['reconciliation'] = __('Reconciliation');
        }

        return $tabs;
    }

    public function resolveTab(Request $request, ?User $user = null): string
    {
        $user ??= $request->user();
        $tab = (string) $request->query('tab', '');

        if ($tab === '') {
            return array_key_first($this->tabs($user)) ?: 'overview';
        }

        $tab = match ($tab) {
            'acquisition-dashboard', 'acquisitions' => 'overview',
            'capitalization-queue' => 'queue',
            'warranty-center' => 'warranties',
            'capitalization-reconciliation' => 'reconciliation',
            default => $tab,
        };

        $allowed = array_keys($this->tabs($user));

        return in_array($tab, $allowed, true) ? $tab : ($allowed[0] ?? 'overview');
    }

    /**
     * @return array<string, mixed>
     */
    protected function queueTab(Request $request): array
    {
        $companyId = (int) tenant()->companyId();
        $query = AssetCapitalizationCandidate::query()
            ->where('company_id', $companyId)
            ->with(['vendor', 'category', 'goodsReceipt', 'purchaseOrder', 'goodsReceiptItem.purchaseOrderItem']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->integer('vendor_id'));
        }

        return [
            'candidates' => $query->latest('received_date')->paginate(config('platform.pagination.default', 15))->withQueryString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function warrantiesTab(Request $request): array
    {
        $query = AssetWarranty::query()
            ->where('company_id', tenant()->companyId())
            ->with(['asset', 'vendor']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return [
            'warranties' => $query->latest('warranty_end')->paginate(config('platform.pagination.default', 15))->withQueryString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function reconciliationTab(): array
    {
        return [
            'records' => AssetCapitalizationReconciliation::query()
                ->where('company_id', tenant()->companyId())
                ->with('runner')
                ->latest('reconciliation_date')
                ->paginate(config('platform.pagination.default', 15)),
        ];
    }
}

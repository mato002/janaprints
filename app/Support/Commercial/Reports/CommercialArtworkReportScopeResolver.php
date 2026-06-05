<?php

namespace App\Support\Commercial\Reports;

use App\Models\Artwork\ArtworkRequest;
use App\Models\Branch;
use App\Models\Crm\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CommercialArtworkReportScopeResolver
{
    public function __construct(
        protected CommercialArtworkReportReadiness $readiness,
    ) {}

    /**
     * @return array{
     *     scope: CommercialArtworkReportScope,
     *     branches: Collection<int, Branch>,
     *     customers: Collection<int, Customer>,
     *     designers: Collection<int, User>,
     *     can_export: bool,
     *     filters: array<string, mixed>,
     *     readiness: list<array<string, mixed>>,
     *     report_ready: bool
     * }
     */
    public function resolve(Request $request): array
    {
        $companyId = tenant()->companyId() ?? $request->user()?->company_id;

        if (! $companyId) {
            abort(403, __('Company context is required.'));
        }

        $branchId = null;
        if ($request->has('branch_id')) {
            $branchId = $request->input('branch_id') !== '' ? (int) $request->input('branch_id') : null;
        } else {
            $branchId = tenant()->branchId();
        }

        $approvalStatus = $request->filled('approval_status') ? (string) $request->input('approval_status') : null;
        if ($approvalStatus !== null && ! in_array($approvalStatus, ['approved', 'rejected', 'revision_requested'], true)) {
            $approvalStatus = null;
        }

        $delayStatus = $request->filled('delay_status') ? (string) $request->input('delay_status') : null;
        if ($delayStatus !== null && ! in_array($delayStatus, ['delayed', 'on_time'], true)) {
            $delayStatus = null;
        }

        $scope = new CommercialArtworkReportScope(
            companyId: (int) $companyId,
            branchId: $branchId,
            fromDate: (string) $request->input('from_date', now()->startOfMonth()->toDateString()),
            toDate: (string) $request->input('to_date', now()->toDateString()),
            customerId: $request->filled('customer_id') ? (int) $request->input('customer_id') : null,
            designerId: $request->filled('designer_id') ? (int) $request->input('designer_id') : null,
            status: $request->filled('status') ? (string) $request->input('status') : null,
            approvalStatus: $approvalStatus,
            delayStatus: $delayStatus,
            search: trim((string) $request->input('search', '')),
            tab: (string) $request->input('tab', 'requests'),
            page: max(1, (int) $request->input('page', 1)),
        );

        $branches = Branch::query()
            ->where('company_id', $scope->companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $customerIds = ArtworkRequest::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->distinct()
            ->pluck('customer_id');

        $customers = $customerIds->isEmpty()
            ? collect()
            : Customer::query()
                ->where('company_id', $scope->companyId)
                ->whereIn('id', $customerIds)
                ->orderBy('company_name')
                ->get(['id', 'company_name', 'customer_code']);

        $designerIds = ArtworkRequest::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereNotNull('assigned_designer_id')
            ->distinct()
            ->pluck('assigned_designer_id');

        $designers = $designerIds->isEmpty()
            ? collect()
            : User::query()
                ->where('company_id', $scope->companyId)
                ->whereIn('id', $designerIds)
                ->orderBy('name')
                ->get(['id', 'name']);

        $user = $request->user();

        return [
            'scope' => $scope,
            'branches' => $branches,
            'customers' => $customers,
            'designers' => $designers,
            'can_export' => $user?->can('commercial.reports.export') ?? false,
            'filters' => [
                'from_date' => $scope->fromDate,
                'to_date' => $scope->toDate,
                'branch_id' => $scope->branchId,
                'customer_id' => $scope->customerId,
                'designer_id' => $scope->designerId,
                'status' => $scope->status,
                'approval_status' => $scope->approvalStatus,
                'delay_status' => $scope->delayStatus,
                'search' => $scope->search,
                'tab' => $scope->tab,
                'page' => $scope->page,
            ],
            'readiness' => $this->readiness->assess(),
            'report_ready' => $this->readiness->isReady(),
        ];
    }
}

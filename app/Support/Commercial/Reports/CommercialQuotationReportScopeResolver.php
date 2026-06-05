<?php

namespace App\Support\Commercial\Reports;

use App\Models\Branch;
use App\Models\Crm\Customer;
use App\Models\Sales\Quotation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CommercialQuotationReportScopeResolver
{
    public function __construct(
        protected CommercialQuotationReportReadiness $readiness,
    ) {}

    /**
     * @return array{
     *     scope: CommercialQuotationReportScope,
     *     branches: Collection<int, Branch>,
     *     customers: Collection<int, Customer>,
     *     salespersons: Collection<int, User>,
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

        $expiryStatus = $request->filled('expiry_status') ? (string) $request->input('expiry_status') : null;
        if (! in_array($expiryStatus, ['expired', 'valid', 'expiring_soon', null], true)) {
            $expiryStatus = null;
        }

        $scope = new CommercialQuotationReportScope(
            companyId: (int) $companyId,
            branchId: $branchId,
            fromDate: (string) $request->input('from_date', now()->startOfMonth()->toDateString()),
            toDate: (string) $request->input('to_date', now()->toDateString()),
            customerId: $request->filled('customer_id') ? (int) $request->input('customer_id') : null,
            salespersonId: $request->filled('salesperson_id') ? (int) $request->input('salesperson_id') : null,
            status: $request->filled('status') ? (string) $request->input('status') : null,
            expiryStatus: $expiryStatus,
            search: trim((string) $request->input('search', '')),
            tab: (string) $request->input('tab', 'summary'),
            page: max(1, (int) $request->input('page', 1)),
        );

        $branches = Branch::query()
            ->where('company_id', $scope->companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $customers = Customer::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->orderBy('company_name')
            ->limit(500)
            ->get(['id', 'company_name']);

        $salespersonIds = Quotation::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->distinct()
            ->pluck('prepared_by');

        $salespersons = $salespersonIds->isEmpty()
            ? collect()
            : User::query()
                ->where('company_id', $scope->companyId)
                ->where('is_active', true)
                ->whereIn('id', $salespersonIds)
                ->orderBy('name')
                ->get(['id', 'name']);

        $user = $request->user();

        return [
            'scope' => $scope,
            'branches' => $branches,
            'customers' => $customers,
            'salespersons' => $salespersons,
            'can_export' => $user?->can('commercial.reports.export') ?? false,
            'filters' => [
                'from_date' => $scope->fromDate,
                'to_date' => $scope->toDate,
                'branch_id' => $scope->branchId,
                'customer_id' => $scope->customerId,
                'salesperson_id' => $scope->salespersonId,
                'status' => $scope->status,
                'expiry_status' => $scope->expiryStatus,
                'search' => $scope->search,
                'tab' => $scope->tab,
                'page' => $scope->page,
            ],
            'readiness' => $this->readiness->assess(),
            'report_ready' => $this->readiness->isReady(),
        ];
    }
}

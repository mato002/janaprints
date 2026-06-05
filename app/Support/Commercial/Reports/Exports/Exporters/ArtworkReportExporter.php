<?php

namespace App\Support\Commercial\Reports\Exports\Exporters;

use App\Enums\ArtworkRequestStatus;
use App\Models\CommercialReportExport;
use App\Support\Commercial\Reports\CommercialArtworkReportQueries;
use App\Support\Commercial\Reports\CommercialArtworkReportScope;
use App\Support\Commercial\Reports\Exports\CommercialReportExportPaginator;
use App\Support\Commercial\Reports\Exports\Contracts\CommercialReportExporter;
use Generator;

class ArtworkReportExporter implements CommercialReportExporter
{
    public function __construct(
        protected CommercialArtworkReportQueries $queries,
    ) {}

    public function module(): string
    {
        return 'artwork';
    }

    public function columns(CommercialReportExport $export): array
    {
        return match ($export->tab) {
            'pending', 'delays' => ['Request', 'Title', 'Customer', 'Branch', 'Designer', 'Priority', 'Status', 'Due', 'Delay', 'Created'],
            'approved', 'rejected' => ['Request', 'Title', 'Customer', 'Branch', 'Designer', 'Priority', 'Status', 'Due', 'Versions', 'Created'],
            'turnaround' => ['Request', 'Title', 'Customer', 'Created', 'Approved', 'Turnaround'],
            'designer_performance' => ['Designer', 'Assigned', 'Completed', 'Pending', 'Throughput'],
            'revision_analysis' => ['Request', 'Title', 'Customer', 'Versions', 'Revisions', 'Status'],
            'by_customer' => ['Customer', 'Requests', 'Pending', 'Approved', 'Rejected'],
            'by_branch' => ['Branch', 'Requests', 'Pending', 'Approved', 'Rejected'],
            default => ['Request', 'Title', 'Customer', 'Branch', 'Designer', 'Priority', 'Status', 'Due', 'Versions', 'Created'],
        };
    }

    public function rows(CommercialReportExport $export): Generator
    {
        $scope = $this->buildScope($export);

        return match ($export->tab) {
            'pending' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateRequestList($this->withPage($scope, $page), $this->queries->pendingStatuses(), true)
            ),
            'approved' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateRequestList($this->withPage($scope, $page), [ArtworkRequestStatus::Approved->value])
            ),
            'rejected' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateRequestList($this->withPage($scope, $page), [ArtworkRequestStatus::Rejected->value])
            ),
            'turnaround' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateTurnaround($this->withPage($scope, $page))
            ),
            'designer_performance' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateDesignerPerformance($this->withPage($scope, $page))
            ),
            'revision_analysis' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateRevisionAnalysis($this->withPage($scope, $page))
            ),
            'by_customer' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateByCustomer($this->withPage($scope, $page))
            ),
            'by_branch' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateByBranch($this->withPage($scope, $page))
            ),
            'delays' => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateDelays($this->withPage($scope, $page))
            ),
            default => CommercialReportExportPaginator::yieldPages(
                fn (int $page) => $this->queries->paginateRequestList($this->withPage($scope, $page))
            ),
        };
    }

    public function title(CommercialReportExport $export): string
    {
        return __('Artwork Report');
    }

    public function subtitle(CommercialReportExport $export): string
    {
        $scope = $this->buildScope($export);

        return __('Period: :from — :to', ['from' => $scope->fromDate, 'to' => $scope->toDate]);
    }

    protected function buildScope(CommercialReportExport $export): CommercialArtworkReportScope
    {
        $payload = $export->scope_payload ?? [];

        return new CommercialArtworkReportScope(
            companyId: (int) ($payload['company_id'] ?? $export->company_id),
            branchId: $payload['branch_id'] ?? null,
            fromDate: (string) ($payload['from_date'] ?? now()->toDateString()),
            toDate: (string) ($payload['to_date'] ?? now()->toDateString()),
            customerId: $payload['customer_id'] ?? null,
            designerId: $payload['designer_id'] ?? null,
            status: $payload['status'] ?? null,
            approvalStatus: $payload['approval_status'] ?? null,
            delayStatus: $payload['delay_status'] ?? null,
            search: (string) ($payload['search'] ?? ''),
            tab: $export->tab,
        );
    }

    protected function withPage(CommercialArtworkReportScope $scope, int $page): CommercialArtworkReportScope
    {
        return new CommercialArtworkReportScope(
            companyId: $scope->companyId,
            branchId: $scope->branchId,
            fromDate: $scope->fromDate,
            toDate: $scope->toDate,
            customerId: $scope->customerId,
            designerId: $scope->designerId,
            status: $scope->status,
            approvalStatus: $scope->approvalStatus,
            delayStatus: $scope->delayStatus,
            search: $scope->search,
            tab: $scope->tab,
            page: $page,
        );
    }
}

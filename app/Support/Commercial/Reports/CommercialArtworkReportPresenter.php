<?php

namespace App\Support\Commercial\Reports;

use App\Enums\ArtworkRequestStatus;
use App\Support\Platform\PlatformCacheService;
use Illuminate\Http\Request;

class CommercialArtworkReportPresenter
{
    public function __construct(
        protected CommercialArtworkReportScopeResolver $scopeResolver,
        protected CommercialArtworkReportQueries $queries,
        protected PlatformCacheService $cache,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Request $request): array
    {
        $resolved = $this->scopeResolver->resolve($request);
        $scope = $resolved['scope'];

        return [
            'title' => __('Artwork Reports'),
            'description' => __('Commercial artwork throughput, approvals, revisions, and designer performance from operational artwork requests.'),
            'filters' => $resolved['filters'],
            'branches' => $resolved['branches'],
            'customers' => $resolved['customers'],
            'designers' => $resolved['designers'],
            'can_export' => $resolved['can_export'],
            'readiness' => $resolved['readiness'],
            'report_ready' => $resolved['report_ready'],
            'kpis' => $resolved['report_ready'] ? $this->cachedKpis($scope) : $this->emptyKpis(),
            'tabs' => $this->tabs(),
            'active_tab' => $scope->tab,
            'tab_data' => $this->presentTab($scope),
        ];
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public function tabs(): array
    {
        return [
            ['key' => 'requests', 'label' => __('Artwork Requests')],
            ['key' => 'pending', 'label' => __('Artwork Pending')],
            ['key' => 'approved', 'label' => __('Artwork Approved')],
            ['key' => 'rejected', 'label' => __('Artwork Rejected')],
            ['key' => 'turnaround', 'label' => __('Artwork Turnaround Time')],
            ['key' => 'designer_performance', 'label' => __('Designer Performance')],
            ['key' => 'revision_analysis', 'label' => __('Revision Analysis')],
            ['key' => 'by_customer', 'label' => __('Artwork By Customer')],
            ['key' => 'by_branch', 'label' => __('Artwork By Branch')],
            ['key' => 'delays', 'label' => __('Artwork Delays')],
        ];
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function cachedKpis(CommercialArtworkReportScope $scope): array
    {
        return $this->cache->remember(
            'dashboard',
            "commercial-artwork-kpis:{$scope->companyId}:{$scope->cacheKey()}",
            fn () => $this->buildKpis($scope),
            (int) config('platform.cache.dashboard', 60),
        );
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function buildKpis(CommercialArtworkReportScope $scope): array
    {
        $approvalRate = $this->queries->approvalRatePercent($scope);
        $avgApproval = $this->queries->averageApprovalTimeHours($scope);

        return [
            ['label' => __('Total Requests'), 'value' => (string) $this->queries->totalRequests($scope), 'icon' => 'color-swatch'],
            ['label' => __('Pending'), 'value' => (string) $this->queries->pendingRequests($scope), 'icon' => 'clock'],
            ['label' => __('Approved'), 'value' => (string) $this->queries->approvedRequests($scope), 'icon' => 'check-circle'],
            ['label' => __('Approval Rate'), 'value' => $approvalRate !== null ? $approvalRate.'%' : '—', 'icon' => 'chart-pie'],
            ['label' => __('Avg Approval Time'), 'value' => $this->queries->formatHours($avgApproval), 'icon' => 'calendar'],
        ];
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function emptyKpis(): array
    {
        $labels = [
            [__('Total Requests'), 'color-swatch'],
            [__('Pending'), 'clock'],
            [__('Approved'), 'check-circle'],
            [__('Approval Rate'), 'chart-pie'],
            [__('Avg Approval Time'), 'calendar'],
        ];

        return collect($labels)->map(fn (array $item) => [
            'label' => $item[0],
            'value' => '—',
            'icon' => $item[1],
            'hint' => __('Awaiting operational data sources'),
        ])->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function presentTab(CommercialArtworkReportScope $scope): array
    {
        if (! $this->queries->hasTable('artwork_requests')) {
            return [
                'type' => 'placeholder',
                'message' => __('Artwork request data is not available yet.'),
            ];
        }

        return match ($scope->tab) {
            'pending' => [
                'type' => 'table',
                'columns' => [__('Request'), __('Title'), __('Customer'), __('Branch'), __('Designer'), __('Priority'), __('Status'), __('Due'), __('Delay'), __('Created')],
                'rows' => $this->queries->paginateRequestList($scope, $this->queries->pendingStatuses(), true),
            ],
            'approved' => [
                'type' => 'table',
                'columns' => [__('Request'), __('Title'), __('Customer'), __('Branch'), __('Designer'), __('Priority'), __('Status'), __('Due'), __('Versions'), __('Created')],
                'rows' => $this->queries->paginateRequestList($scope, [ArtworkRequestStatus::Approved->value]),
            ],
            'rejected' => [
                'type' => 'table',
                'columns' => [__('Request'), __('Title'), __('Customer'), __('Branch'), __('Designer'), __('Priority'), __('Status'), __('Due'), __('Versions'), __('Created')],
                'rows' => $this->queries->paginateRequestList($scope, [ArtworkRequestStatus::Rejected->value]),
            ],
            'turnaround' => [
                'type' => 'table',
                'columns' => [__('Request'), __('Title'), __('Customer'), __('Created'), __('Approved'), __('Turnaround')],
                'rows' => $this->queries->paginateTurnaround($scope),
            ],
            'designer_performance' => [
                'type' => 'table',
                'columns' => [__('Designer'), __('Assigned'), __('Completed'), __('Pending'), __('Throughput')],
                'rows' => $this->queries->paginateDesignerPerformance($scope),
            ],
            'revision_analysis' => [
                'type' => 'table',
                'columns' => [__('Request'), __('Title'), __('Customer'), __('Versions'), __('Revisions'), __('Status')],
                'rows' => $this->queries->paginateRevisionAnalysis($scope),
            ],
            'by_customer' => [
                'type' => 'table',
                'columns' => [__('Customer'), __('Requests'), __('Pending'), __('Approved'), __('Rejected')],
                'rows' => $this->queries->paginateByCustomer($scope),
            ],
            'by_branch' => [
                'type' => 'table',
                'columns' => [__('Branch'), __('Requests'), __('Pending'), __('Approved'), __('Rejected')],
                'rows' => $this->queries->paginateByBranch($scope),
            ],
            'delays' => [
                'type' => 'table',
                'columns' => [__('Request'), __('Title'), __('Customer'), __('Branch'), __('Designer'), __('Priority'), __('Status'), __('Due'), __('Delay'), __('Created')],
                'rows' => $this->queries->paginateDelays($scope),
            ],
            default => [
                'type' => 'table',
                'columns' => [__('Request'), __('Title'), __('Customer'), __('Branch'), __('Designer'), __('Priority'), __('Status'), __('Due'), __('Versions'), __('Created')],
                'rows' => $this->queries->paginateRequestList($scope),
            ],
        };
    }
}

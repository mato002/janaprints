@php
    use App\Services\Production\ProductionJobCardIndexService;

    $indexService = app(ProductionJobCardIndexService::class);
    $jobCards = $job_cards ?? null;
    $filters = $filters ?? [];
    $filterOptions = $filter_options ?? [];
    $activeChips = $active_filter_chips ?? [];
    $kpis = $kpis ?? [];
    $pipeline = $pipeline ?? [];
    $alerts = $alerts ?? [];
    $workload = $workload ?? [];
    $quickActions = $quick_actions ?? [];
    $bulkActions = $bulk_actions ?? [];
@endphp

<x-admin-layout
    :title="__('Job cards')"
    :breadcrumbs="[
        ['label' => __('Production'), 'url' => route('admin.production.dashboard')],
        ['label' => __('Job cards')],
    ]"
>
    <x-admin.page-header
        :title="__('Production Operations Command Center')"
        :description="__('Production intelligence from existing job, queue, QC, and dispatch data — no duplicate workflows.')"
    />

    @include('admin.production.job-cards.command-center.kpi-strip', ['kpis' => $kpis])

    @include('admin.production.job-cards.command-center.pipeline', ['pipeline' => $pipeline])

    @include('admin.production.job-cards.command-center.quick-actions', ['quickActions' => $quickActions])

    <div class="job-cards-cc">
        <div class="job-cards-cc__main space-y-4">
            @include('admin.production.job-cards.command-center.filters', [
                'filters' => $filters,
                'filterOptions' => $filterOptions,
                'activeChips' => $activeChips,
            ])

            @include('admin.production.job-cards.command-center.table', [
                'jobCards' => $jobCards,
                'indexService' => $indexService,
                'filters' => $filters,
                'bulkActions' => $bulkActions,
            ])
        </div>

        <aside class="job-cards-cc__rail space-y-4">
            @include('admin.production.job-cards.command-center.alerts', ['alerts' => $alerts])
            @include('admin.production.job-cards.command-center.workload', ['workload' => $workload])
        </aside>
    </div>
</x-admin-layout>

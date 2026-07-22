@php
    $pageTitle = $active_department_label
        ? __(':department Command Centre', ['department' => $active_department_label])
        : __('Production Command Centre');
    $indexRoute = $active_department
        ? route('admin.production.queue.department', $active_department)
        : route('admin.production.queue.index');
    $commandMetrics = $command_metrics ?? $metrics ?? [];
@endphp

<x-admin-layout
    :title="$pageTitle"
    :breadcrumbs="[
        ['label' => __('Production'), 'url' => route('admin.workspaces.production')],
        ['label' => __('Command Centres'), 'url' => route('admin.production.queue.index')],
        ...($active_department_label ? [['label' => $active_department_label]] : []),
    ]"
>
    <x-admin.page-header
        :title="$pageTitle"
        :description="__('Daily department jobs register — defaults to jobs logged today, matching the legacy shop floor sheets.')"
    >
        <x-slot name="actions">
            <x-admin.export-dropdown
                export-route="admin.production.queue.export"
                :export-query="array_merge(
                    collect($filters)->filter(fn ($value) => filled($value))->all(),
                    $active_department ? ['department' => $active_department] : []
                )"
            />
        </x-slot>
    </x-admin.page-header>

    @include('admin.production.queue.partials.department-nav', [
        'departmentNav' => $department_nav,
    ])

    @include('admin.production.queue.partials.metrics-strip', [
        'metrics' => $commandMetrics,
    ])

    <x-admin.card :padding="false" class="mb-4 sticky top-0 z-20 shadow-sm">
        @include('admin.production.queue.partials.toolbar', [
            'indexRoute' => $indexRoute,
            'filters' => $filters,
            'workCenters' => $workCenters,
            'operators' => $operators,
            'machines' => $machines,
            'customers' => $customers,
            'workspace' => $workspace,
        ])
    </x-admin.card>

    <x-admin.card :padding="false">
        @include('admin.production.queue.partials.table', [
            'queues' => $queues,
            'workspace' => $workspace,
            'commandCenter' => $command_center ?? null,
            'columns' => $columns ?? [],
            'activeDepartment' => $active_department,
        ])
    </x-admin.card>

    @include('admin.production.queue.partials.summary', [
        'summary' => $summary ?? [],
    ])
</x-admin-layout>

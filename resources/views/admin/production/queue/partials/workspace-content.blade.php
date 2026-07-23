@php
    use App\Support\Production\ProductionFloorDeskViews;

    $pageTitle = $active_department_label
        ? __(':department queue', ['department' => $active_department_label])
        : __('By department');
    $indexRoute = ProductionFloorDeskViews::queueIndexUrl($active_department ?: null);
    $commandMetrics = $command_metrics ?? $metrics ?? [];
@endphp

@if (! ($embeddedInFloor ?? false))
    <x-admin.page-header
        :title="$pageTitle"
        :description="__('Daily department jobs — defaults to jobs logged today.')"
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
@else
    <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-sm font-semibold text-erp-primary">{{ $pageTitle }}</h2>
            <p class="text-xs text-slate-600">{{ __('Daily department jobs — defaults to jobs logged today.') }}</p>
        </div>
        <x-admin.export-dropdown
            export-route="admin.production.queue.export"
            :export-query="array_merge(
                collect($filters)->filter(fn ($value) => filled($value))->all(),
                $active_department ? ['department' => $active_department] : []
            )"
        />
    </div>
@endif

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
        'activeDepartment' => $active_department,
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

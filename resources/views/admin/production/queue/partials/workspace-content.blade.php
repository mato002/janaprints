@php
    use App\Support\Production\ProductionDeskPersona;
    use App\Support\Production\ProductionFloorDeskViews;

    $pageTitle = $active_department_label
        ? __(':department queue', ['department' => $active_department_label])
        : __('By department');
    $indexRoute = ProductionFloorDeskViews::queueIndexUrl($active_department ?: null);
    $commandMetrics = $command_metrics ?? $metrics ?? [];
    $persona = $deskPersona ?? ProductionDeskPersona::resolve(auth()->user());
    $hideDepartmentPills = $persona->usesDepartmentOperationsModes() && ($embeddedInFloor ?? false);
    $dense = (bool) ($embeddedInFloor ?? false);
    $useRibbon = $dense || $persona->usesDepartmentOperationsModes();

    $departmentTabs = [];
    if ($useRibbon) {
        if ($hideDepartmentPills) {
            $departmentTabs = collect($persona->standaloneFloorModes($active_department ?? null))
                ->reject(fn (array $mode) => $mode['key'] === ProductionFloorDeskViews::FLOOR)
                ->map(fn (array $mode) => [
                    'key' => $mode['key'],
                    'label' => $mode['label'],
                    'url' => $mode['url'],
                    'active' => ($active_department ?? null) === $mode['key'],
                ])
                ->values()
                ->all();
        } else {
            $departmentTabs = collect($department_nav ?? [])
                ->reject(fn (array $item) => ($item['slug'] ?? '') === '')
                ->map(fn (array $item) => [
                    'key' => $item['slug'],
                    'label' => $item['label'],
                    'url' => $item['url'],
                    'active' => $item['active'] ?? false,
                ])
                ->values()
                ->all();
        }
    }
@endphp

<div @class([
    'production-queue-workspace flex min-h-0 flex-1 flex-col',
    'production-queue-workspace--dense' => $dense,
    'production-queue-workspace--ribbon' => $useRibbon,
])>
    @unless ($dense || $useRibbon)
        <x-admin.page-header
            :title="$pageTitle"
            :description="__('Daily department jobs — defaults to jobs logged today.')"
        />
    @endunless

    @if ($useRibbon)
        @include('admin.production.queue.partials.production-queue-ribbon', [
            'departmentTabs' => $departmentTabs,
            'commandMetrics' => $commandMetrics,
            'summary' => $summary ?? [],
            'filters' => $filters ?? [],
            'activeDepartment' => $active_department ?? null,
            'indexRoute' => $indexRoute,
            'workCenters' => $workCenters,
            'operators' => $operators,
            'machines' => $machines,
            'customers' => $customers,
            'workspace' => $workspace,
        ])
    @else
        @include('admin.production.queue.partials.department-nav', [
            'departmentNav' => $department_nav,
        ])

        <div class="production-queue-workspace__chrome sticky top-0 z-20 shrink-0 bg-erp-page">
            @include('admin.production.queue.partials.metrics-strip', [
                'metrics' => $commandMetrics,
                'compact' => $dense,
                'summary' => $summary ?? [],
                'filters' => $filters ?? [],
                'activeDepartment' => $active_department ?? null,
            ])

            <div class="overflow-hidden rounded-md border border-erp-border bg-white shadow-sm">
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
            </div>
        </div>
    @endif

    <div class="production-queue-workspace__table mt-2 min-h-0 flex-1 overflow-hidden rounded-md border border-erp-border bg-white shadow-sm">
        @include('admin.production.queue.partials.table', [
            'queues' => $queues,
            'workspace' => $workspace,
            'commandCenter' => $command_center ?? null,
            'columns' => $columns ?? [],
            'activeDepartment' => $active_department,
            'dense' => $dense,
        ])
    </div>

    @unless ($dense)
        @include('admin.production.queue.partials.summary', [
            'summary' => $summary ?? [],
        ])
    @endunless
</div>

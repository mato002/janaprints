@php
    use App\Models\Assets\FixedAsset;
    use App\Support\Production\ProductionFloorDeskViews;

    $operatorMode = (bool) ($operatorMode ?? false);
    $activeFloorView = ProductionFloorDeskViews::normalize($activeFloorView ?? request('view'));
    $embeddedInFloor = (bool) ($embeddedInFloor ?? ($activeFloorView !== ProductionFloorDeskViews::FLOOR));

    $machinesForUi = [];
    if ($activeFloorView === ProductionFloorDeskViews::FLOOR) {
        $machineMeta = FixedAsset::query()
            ->forTenant()
            ->whereHas('machineProfile')
            ->with('machineProfile:id,fixed_asset_id,production_status')
            ->orderBy('asset_name')
            ->get(['id', 'asset_name'])
            ->mapWithKeys(function ($machine) {
                $status = $machine->machineProfile?->production_status;

                return [
                    (string) $machine->id => [
                        'status' => $status?->value,
                        'status_label' => $status?->label(),
                        'icon' => match ($status?->value) {
                            'available' => '🟢',
                            'running', 'idle' => '🟡',
                            'maintenance' => '🔴',
                            'offline', 'retired' => '⚪',
                            default => '⚪',
                        },
                    ],
                ];
            });

        $machinesForUi = collect($filter_options['machines'] ?? [])->map(function ($machine) use ($machineMeta) {
            $meta = $machineMeta[(string) $machine['value']] ?? null;
            $label = $machine['label'];

            if ($meta) {
                $label = trim(($meta['icon'] ?? '').' '.$machine['label'].' · '.($meta['status_label'] ?? ''));
            }

            return [
                'value' => $machine['value'],
                'label' => $label,
            ];
        })->values()->all();
    }
@endphp

<x-admin-layout
    :title="$operatorMode ? __('Operator Floor') : __('Production Floor')"
    :breadcrumbs="$operatorMode
        ? [['label' => __('Operator Floor')]]
        : [
            ['label' => __('Production'), 'url' => route('admin.workspaces.production')],
            ['label' => __('Production Floor')],
        ]"
    :compact-page="false"
>
    <div class="production-floor-shell">
        @if ($operatorMode)
            <div class="mb-3 flex flex-col gap-2 rounded-lg border border-erp-accent/25 bg-erp-accent/5 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-erp-primary">{{ __('Operator mode') }}</p>
                    <p class="text-xs text-slate-600">{{ __('Work arrives here — use Next step on each job. Preview orders and jobs in modals without leaving the floor.') }}</p>
                </div>
            </div>
        @elseif ($activeFloorView === ProductionFloorDeskViews::FLOOR && ! \App\Support\Navigation\WorkspaceEmbed::inWorkspaceContext())
            <x-admin.page-header
                :title="__('Production Floor')"
                :description="__('Production queue — work arrives here ready to run. Assign machines, execute stages, and dispatch.')"
            />
        @endif

        @include('admin.production.floor.partials.desk-mode-nav', ['activeFloorView' => $activeFloorView])

        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif

        @if ($activeFloorView === ProductionFloorDeskViews::REGISTER)
            @include('admin.production.job-cards.partials.register-content', ['embeddedInFloor' => true])
        @elseif ($activeFloorView === ProductionFloorDeskViews::QUEUE)
            @include('admin.production.queue.partials.workspace-content', ['embeddedInFloor' => true])
        @elseif ($activeFloorView === ProductionFloorDeskViews::OUTPUTS)
            @include('admin.production.outputs.partials.register-content', ['embeddedInFloor' => true])
        @else
            <div
                class="production-floor"
                x-data="productionFloor(@js([
                    'panelBase' => url('admin/production/floor/jobs'),
                    'initialJobKey' => request('job'),
                    'assignMachineUrl' => url('admin/production/floor/jobs'),
                    'labelUrl' => url('admin/production/job-cards'),
                    'jobCardUrl' => url('admin/production/job-cards'),
                    'csrf' => csrf_token(),
                    'machines' => $machinesForUi,
                    'operatorCreateUrl' => auth()->user()?->can('employees.manage')
                        ? route('admin.operators.quick-create')
                        : null,
                    'operatorsRefreshUrl' => route('admin.lookups.operators'),
                    'modalTitles' => [
                        'operator' => __('Assign operator'),
                        'machine' => __('Assign machine'),
                        'outsource-send' => __('Send to vendor'),
                        'outsource-return' => __('Mark returned from vendor'),
                        'qc' => __('Record inspection'),
                        'fulfilment' => __('Hand off'),
                        'default' => __('Next step'),
                    ],
                ]))"
                x-cloak
            >
                <div class="production-floor-command-sticky" x-ref="commandBar">
                    @include('admin.production.floor.partials.summary-strip', ['summary' => $summary])

                    <x-admin.card :padding="false" class="mb-0 shadow-sm">
                        <x-admin.index-toolbar
                            :action="route('admin.production.floor')"
                            :reset-url="route('admin.production.floor')"
                            data-production-floor-live-filters
                        >
                            @if (request('desk'))
                                <input type="hidden" name="desk" value="{{ request('desk') }}">
                            @endif
                            <input
                                type="search"
                                name="search"
                                value="{{ $filters['search'] }}"
                                class="erp-toolbar-input min-w-[12rem] flex-1"
                                placeholder="{{ __('Job or product…') }}"
                                aria-label="{{ __('Search') }}"
                                data-erp-auto-search
                            >
                            <select name="stage" class="erp-toolbar-select" aria-label="{{ __('Stage') }}" data-erp-auto-submit>
                                <option value="">{{ __('All active stages') }}</option>
                                @foreach ($filter_options['stages'] as $stage)
                                    <option value="{{ $stage['value'] }}" @selected($filters['stage'] === $stage['value'])>
                                        {{ $stage['label'] }}
                                        @if (($stage_counts[$stage['value']] ?? 0) > 0)
                                            ({{ $stage_counts[$stage['value']] }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <select name="machine_id" class="erp-toolbar-select" aria-label="{{ __('Machine') }}" data-erp-auto-submit>
                                <option value="">{{ __('All machines') }}</option>
                                @foreach ($filter_options['machines'] as $machine)
                                    <option value="{{ $machine['value'] }}" @selected($filters['machine_id'] === $machine['value'])>{{ $machine['label'] }}</option>
                                @endforeach
                            </select>
                            <select name="vendor_id" class="erp-toolbar-select" aria-label="{{ __('Vendor') }}" data-erp-auto-submit>
                                <option value="">{{ __('All vendors') }}</option>
                                @foreach ($filter_options['vendors'] as $vendor)
                                    <option value="{{ $vendor['value'] }}" @selected($filters['vendor_id'] === $vendor['value'])>{{ $vendor['label'] }}</option>
                                @endforeach
                            </select>
                            <select name="priority" class="erp-toolbar-select" aria-label="{{ __('Priority') }}" data-erp-auto-submit>
                                <option value="">{{ __('All priorities') }}</option>
                                @foreach ($filter_options['priorities'] as $priority)
                                    <option value="{{ $priority['value'] }}" @selected($filters['priority'] === $priority['value'])>{{ $priority['label'] }}</option>
                                @endforeach
                            </select>
                            <label class="inline-flex items-center gap-1.5 text-xs text-slate-600">
                                <input type="checkbox" name="overdue" value="1" class="rounded border-slate-300" data-erp-auto-submit @checked($filters['overdue'] === '1')>
                                {{ __('Overdue only') }}
                            </label>
                        </x-admin.index-toolbar>

                        <div class="production-floor-toolbar-extras">
                            <label class="inline-flex items-center gap-1.5 text-xs text-slate-600">
                                <span class="font-medium text-slate-700">{{ __('Group by') }}</span>
                                <select class="erp-toolbar-select text-xs" x-model="groupBy" @change="applyGrouping()" aria-label="{{ __('Group queue by') }}">
                                    <option value="">{{ __('None') }}</option>
                                    <option value="machine">{{ __('Machine') }}</option>
                                    <option value="stage">{{ __('Stage') }}</option>
                                    <option value="priority">{{ __('Priority') }}</option>
                                    <option value="vendor">{{ __('Vendor') }}</option>
                                    <option value="due">{{ __('Due date') }}</option>
                                    <option value="operator">{{ __('Operator / work center') }}</option>
                                    <option value="customer">{{ __('Customer') }}</option>
                                </select>
                            </label>
                        </div>
                    </x-admin.card>
                </div>

                <div
                    class="production-floor-batch-bar"
                    x-ref="batchBar"
                    x-show="selectedJobs.length > 0"
                    x-cloak
                >
                    <span class="production-floor-batch-bar__count" x-text="`${selectedJobs.length} {{ __('selected') }}`"></span>
                    <button type="button" class="erp-btn-secondary text-xs py-1 px-2" @click="openBatchMachineAssign()">{{ __('Assign machine') }}</button>
                    <button type="button" class="erp-btn-secondary text-xs py-1 px-2" @click="batchPrintLabels()">{{ __('Print labels') }}</button>
                    <button type="button" class="erp-btn-secondary text-xs py-1 px-2" @click="batchPrintJobCards()">{{ __('Print job sheets') }}</button>
                    <button type="button" class="erp-btn-ghost text-xs py-1 px-2" @click="clearSelection()">{{ __('Clear') }}</button>
                </div>

                <div
                    x-show="batchMachineOpen"
                    x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    @keydown.escape.window="batchMachineOpen = false"
                >
                    <div class="absolute inset-0 bg-slate-900/40" @click="batchMachineOpen = false"></div>
                    <div class="relative z-10 w-full max-w-md rounded-lg border border-erp-border bg-white p-4 shadow-xl">
                        <h3 class="mb-3 text-sm font-semibold text-erp-primary">{{ __('Assign machine to selected jobs') }}</h3>
                        <select class="erp-select w-full text-sm" x-model="batchMachineId">
                            <option value="">{{ __('Assign') }}</option>
                            <template x-for="machine in machines" :key="machine.value">
                                <option :value="machine.value" x-text="machine.label"></option>
                            </template>
                        </select>
                        <div class="mt-4 flex justify-end gap-2">
                            <button type="button" class="erp-btn-secondary text-sm" @click="batchMachineOpen = false">{{ __('Cancel') }}</button>
                            <button type="button" class="erp-btn-primary text-sm" @click="submitBatchMachineAssign()" :disabled="batchSubmitting">
                                <span x-show="!batchSubmitting">{{ __('Apply') }}</span>
                                <span x-show="batchSubmitting">{{ __('Applying…') }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                @include('admin.production.floor.partials.action-modal', ['operatorMode' => $operatorMode])

                <div class="mb-2 flex items-center justify-between gap-3">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Production Queue') }}</h2>
                    <p class="text-xs text-slate-400">{{ __('No creation — execution only.') }}</p>
                </div>

                @include('admin.production.floor.partials.table', [
                    'rows' => $rows,
                    'filter_options' => $filter_options,
                    'filters' => $filters,
                    'operatorMode' => $operatorMode,
                ])

                <div class="mt-4 pb-6">{{ $jobs->links() }}</div>

                @include('admin.production.floor.partials.job-panel', ['operatorMode' => $operatorMode])
            </div>
        @endif
    </div>
</x-admin-layout>

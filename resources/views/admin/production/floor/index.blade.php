@php
    $operatorMode = (bool) ($operatorMode ?? false);
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
                    <p class="text-xs text-slate-600">{{ __('Use Next step on each job. Preview orders and jobs in modals — stay on this floor.') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if ($can_create && $create_url)
                        <a href="{{ $create_url }}" class="erp-btn-primary" data-erp-modal-open>{{ __('Create job card') }}</a>
                    @endif
                </div>
            </div>
        @else
            <x-admin.page-header
                :title="__('Production Floor')"
                :description="__('Shop floor register — filter by stage, assign machines, and take the next action without hunting through menus.')"
            >
                @if ($can_create && $create_url)
                    <x-slot name="actions">
                        <a href="{{ $create_url }}" class="erp-btn-primary" data-erp-modal-open>{{ __('Create job card') }}</a>
                    </x-slot>
                @endif
            </x-admin.page-header>
        @endif

        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif

        @include('admin.production.floor.partials.summary-strip', ['summary' => $summary])

        <div
            class="production-floor"
            x-data="productionFloor(@js([
                'panelBase' => url('admin/production/floor/jobs'),
                'initialJobKey' => request('job'),
            ]))"
            x-cloak
        >
            <x-admin.card :padding="false" class="mb-4">
                <x-admin.index-toolbar :action="route('admin.production.floor')" :reset-url="route('admin.production.floor')">
                    <input
                        type="search"
                        name="search"
                        value="{{ $filters['search'] }}"
                        class="erp-toolbar-input min-w-[12rem] flex-1"
                        placeholder="{{ __('Job, customer, or product…') }}"
                        aria-label="{{ __('Search') }}"
                        data-erp-auto-search
                    >
                    <select name="stage" class="erp-toolbar-select" aria-label="{{ __('Stage') }}">
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
                    <select name="machine_id" class="erp-toolbar-select" aria-label="{{ __('Machine') }}">
                        <option value="">{{ __('All machines') }}</option>
                        @foreach ($filter_options['machines'] as $machine)
                            <option value="{{ $machine['value'] }}" @selected($filters['machine_id'] === $machine['value'])>{{ $machine['label'] }}</option>
                        @endforeach
                    </select>
                    <select name="vendor_id" class="erp-toolbar-select" aria-label="{{ __('Vendor') }}">
                        <option value="">{{ __('All vendors') }}</option>
                        @foreach ($filter_options['vendors'] as $vendor)
                            <option value="{{ $vendor['value'] }}" @selected($filters['vendor_id'] === $vendor['value'])>{{ $vendor['label'] }}</option>
                        @endforeach
                    </select>
                    <select name="priority" class="erp-toolbar-select" aria-label="{{ __('Priority') }}">
                        <option value="">{{ __('All priorities') }}</option>
                        @foreach ($filter_options['priorities'] as $priority)
                            <option value="{{ $priority['value'] }}" @selected($filters['priority'] === $priority['value'])>{{ $priority['label'] }}</option>
                        @endforeach
                    </select>
                    <label class="inline-flex items-center gap-1.5 text-xs text-slate-600">
                        <input type="checkbox" name="overdue" value="1" class="rounded border-slate-300" @checked($filters['overdue'] === '1')>
                        {{ __('Overdue only') }}
                    </label>
                </x-admin.index-toolbar>
            </x-admin.card>

            @include('admin.production.floor.partials.table', [
                'rows' => $rows,
                'filter_options' => $filter_options,
                'filters' => $filters,
                'operatorMode' => $operatorMode,
            ])

            <div class="mt-4 pb-6">{{ $jobs->links() }}</div>

            @include('admin.production.floor.partials.job-panel', ['operatorMode' => $operatorMode])
        </div>
    </div>
</x-admin-layout>

<x-admin-layout :title="$title">
    <x-admin.page-header :title="$title" :description="$description">
        <x-slot name="actions">
            @include('admin.reports.partials.export-button', [
                'can_export' => $can_export,
                'export_route' => 'admin.reports.legacy.export',
                'export_route_params' => ['reportKey' => $key],
                'format_in_path' => true,
            ])
        </x-slot>
    </x-admin.page-header>

    <x-admin.card :padding="false" class="mb-6">
        <x-admin.index-toolbar :action="url()->current()" :reset-url="url()->current()">
            <input type="date" id="from_date" name="from_date" value="{{ $filters['from_date'] }}" class="erp-toolbar-input" aria-label="{{ __('From') }}">
            <input type="date" id="to_date" name="to_date" value="{{ $filters['to_date'] }}" class="erp-toolbar-input" aria-label="{{ __('To') }}">
            <select id="branch_id" name="branch_id" class="erp-toolbar-select" aria-label="{{ __('Branch') }}">
                <option value="">{{ __('All branches') }}</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null) == $branch->id)>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </x-admin.index-toolbar>
    </x-admin.card>

    <div class="mb-6 erp-kpi-grid">
        @foreach ($widgets as $widget)
            <x-admin.kpi-widget
                :label="$widget['label']"
                :value="$widget['value']"
                :icon="$widget['icon']"
                :hint="$widget['hint']"
            />
        @endforeach
    </div>

    @unless ($has_data)
        <x-admin.card>
            <x-admin.empty-state
                icon="chart-pie"
                :title="__('No report data yet')"
                :description="__('Metrics for this report will appear here once connected data sources are available. Adjust filters and try again later.')"
            />
        </x-admin.card>
    @endunless
</x-admin-layout>

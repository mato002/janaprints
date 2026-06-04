<x-admin-layout :title="$title">
    <x-admin.page-header :title="$title" :description="$description">
        <x-slot name="actions">
            @if ($can_export)
                <button
                    type="button"
                    class="erp-btn-secondary"
                    disabled
                    title="{{ __('Export will be available in a future release') }}"
                >
                    {{ __('Export') }}
                </button>
            @else
                <button
                    type="button"
                    class="erp-btn-secondary opacity-60"
                    disabled
                    title="{{ __('You do not have permission to export reports') }}"
                >
                    {{ __('Export') }}
                </button>
            @endif
        </x-slot>
    </x-admin.page-header>

    <x-admin.card class="mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="text-[11px] text-slate-500" for="from_date">{{ __('From') }}</label>
                <input
                    type="date"
                    id="from_date"
                    name="from_date"
                    value="{{ $filters['from_date'] }}"
                    class="erp-input mt-1"
                >
            </div>
            <div>
                <label class="text-[11px] text-slate-500" for="to_date">{{ __('To') }}</label>
                <input
                    type="date"
                    id="to_date"
                    name="to_date"
                    value="{{ $filters['to_date'] }}"
                    class="erp-input mt-1"
                >
            </div>
            <div>
                <label class="text-[11px] text-slate-500" for="branch_id">{{ __('Branch') }}</label>
                <select id="branch_id" name="branch_id" class="erp-input mt-1 min-w-[10rem]">
                    <option value="">{{ __('All branches') }}</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null) == $branch->id)>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="erp-btn-primary">{{ __('Apply filters') }}</button>
        </form>
    </x-admin.card>

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
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

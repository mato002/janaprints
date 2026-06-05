<x-admin-layout
    :title="__('Scheduling')"
    :breadcrumbs="[
        ['label' => __('Production'), 'url' => route('admin.workspaces.production')],
        ['label' => __('Scheduling')],
    ]"
>
    <x-admin.page-header
        :title="__('Scheduling')"
        :description="__('Plan production jobs by date and work center using job card planned dates.')"
    />

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4 mb-6">
        @foreach ([
            ['label' => __('Scheduled Jobs'), 'value' => $kpis['scheduled'], 'icon' => 'calendar'],
            ['label' => __('Unscheduled Jobs'), 'value' => $kpis['unscheduled'], 'icon' => 'clock'],
            ['label' => __('Overdue Jobs'), 'value' => $kpis['overdue'], 'icon' => 'exclamation'],
            ['label' => __('Upcoming Jobs'), 'value' => $kpis['upcoming'], 'icon' => 'sparkles'],
        ] as $kpi)
            <x-admin.kpi-widget :label="$kpi['label']" :value="(string) $kpi['value']" :icon="$kpi['icon']" />
        @endforeach
    </div>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="inline-flex rounded-lg border border-erp-border bg-white p-0.5 text-sm">
            <a
                href="{{ route('admin.production.scheduling.index', array_merge(request()->except('page'), ['view' => 'list'])) }}"
                class="rounded-md px-3 py-1.5 {{ $viewMode === 'list' ? 'bg-erp-primary text-white' : 'text-slate-600 hover:bg-slate-50' }}"
            >
                {{ __('List view') }}
            </a>
            <a
                href="{{ route('admin.production.scheduling.index', array_merge(request()->except('page'), ['view' => 'calendar'])) }}"
                class="rounded-md px-3 py-1.5 {{ $viewMode === 'calendar' ? 'bg-erp-primary text-white' : 'text-slate-600 hover:bg-slate-50' }}"
            >
                {{ __('Calendar view') }}
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.production.scheduling.index') }}" class="mb-4 flex flex-wrap items-end gap-3">
        <input type="hidden" name="view" value="{{ $viewMode }}">
        @if ($viewMode === 'calendar')
            <input type="hidden" name="month" value="{{ $filters['month'] }}">
        @endif
        <div class="min-w-[10rem] flex-1">
            <label class="text-xs text-slate-600" for="search">{{ __('Search') }}</label>
            <input
                id="search"
                type="search"
                name="search"
                value="{{ $filters['search'] }}"
                class="erp-input mt-1 w-full text-sm"
                placeholder="{{ __('Job number or customer…') }}"
            >
        </div>
        <div>
            <label class="text-xs text-slate-600" for="status">{{ __('Job Status') }}</label>
            <select id="status" name="status" class="erp-select mt-1">
                <option value="">{{ __('All') }}</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>
                        {{ str_replace('_', ' ', ucfirst($status->value)) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs text-slate-600" for="priority">{{ __('Priority') }}</label>
            <select id="priority" name="priority" class="erp-select mt-1">
                <option value="">{{ __('All') }}</option>
                @foreach ($priorities as $priority)
                    <option value="{{ $priority->value }}" @selected($filters['priority'] === $priority->value)>
                        {{ str_replace('_', ' ', ucfirst($priority->value)) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs text-slate-600" for="work_center_id">{{ __('Work Center') }}</label>
            <select id="work_center_id" name="work_center_id" class="erp-select mt-1">
                <option value="">{{ __('All') }}</option>
                @foreach ($workCenters as $center)
                    <option value="{{ $center->id }}" @selected((string) $filters['work_center_id'] === (string) $center->id)>{{ $center->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs text-slate-600" for="date_from">{{ __('From') }}</label>
            <input id="date_from" type="date" name="date_from" value="{{ $filters['date_from'] }}" class="erp-input mt-1 text-sm">
        </div>
        <div>
            <label class="text-xs text-slate-600" for="date_to">{{ __('To') }}</label>
            <input id="date_to" type="date" name="date_to" value="{{ $filters['date_to'] }}" class="erp-input mt-1 text-sm">
        </div>
        <x-secondary-button type="submit">{{ __('Filter') }}</x-secondary-button>
        @if ($filters['status'] || $filters['priority'] || $filters['work_center_id'] || $filters['date'] || $filters['date_from'] || $filters['date_to'] || $filters['search'])
            <a href="{{ route('admin.production.scheduling.index', ['view' => $viewMode]) }}" class="text-sm text-slate-600 hover:text-erp-primary">{{ __('Clear') }}</a>
        @endif
    </form>

    @if ($viewMode === 'calendar' && $calendar)
        @include('admin.production.scheduling.partials.calendar', ['calendar' => $calendar, 'filters' => $filters])
    @else
        <section aria-label="{{ __('Schedule register') }}">
            <h2 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Schedule Register') }}</h2>
            @include('admin.production.scheduling.partials.list', ['jobs' => $jobs, 'workspace' => $workspace])
        </section>
    @endif
</x-admin-layout>

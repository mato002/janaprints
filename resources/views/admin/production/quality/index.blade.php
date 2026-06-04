<x-admin-layout
    :title="__('Quality Control')"
    :breadcrumbs="[
        ['label' => __('Production'), 'url' => route('admin.workspaces.production')],
        ['label' => __('Quality Control')],
    ]"
>
    <x-admin.page-header
        :title="__('Quality Control')"
        :description="__('Inspection register and QC status across production jobs.')"
    />

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4 mb-6">
        @foreach ([
            ['label' => __('Pending Inspections'), 'value' => $kpis['pending_inspections'], 'icon' => 'clock'],
            ['label' => __('Passed'), 'value' => $kpis['passed'], 'icon' => 'check-circle'],
            ['label' => __('Failed'), 'value' => $kpis['failed'], 'icon' => 'exclamation'],
            ['label' => __('On Hold'), 'value' => $kpis['on_hold'], 'icon' => 'shield-check'],
        ] as $kpi)
            <x-admin.kpi-widget :label="$kpi['label']" :value="(string) $kpi['value']" :icon="$kpi['icon']" />
        @endforeach
    </div>

    <section class="mb-6" aria-label="{{ __('Analytics') }}">
        <h2 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Analytics') }}</h2>
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <x-admin.kpi-widget
                :label="__('Pass Rate')"
                :value="number_format($analytics['pass_rate'], 1).'%'"
                :hint="__('Of :count inspections', ['count' => $analytics['total_inspections']])"
                icon="check-circle"
            />
            <x-admin.kpi-widget
                :label="__('Fail Rate')"
                :value="number_format($analytics['fail_rate'], 1).'%'"
                :hint="__('Of :count inspections', ['count' => $analytics['total_inspections']])"
                icon="exclamation"
            />
            <x-admin.kpi-widget
                :label="__('Rework Count')"
                :value="(string) $analytics['rework_count']"
                :hint="__('Rework required results')"
                icon="switch-horizontal"
            />
            <x-admin.kpi-widget
                :label="__('Hold Count')"
                :value="(string) $analytics['hold_count']"
                :hint="__('Jobs on hold')"
                icon="shield-check"
            />
        </div>
    </section>

    @include('admin.production.quality.partials.intelligence', ['widgets' => $widgets])

    <form method="GET" action="{{ route('admin.production.quality.index') }}" class="mb-4 flex flex-wrap items-end gap-3">
        <div class="min-w-[10rem] flex-1">
            <label class="text-xs text-slate-600" for="search">{{ __('Search') }}</label>
            <input
                id="search"
                type="search"
                name="search"
                value="{{ $filters['search'] }}"
                class="erp-input mt-1 w-full text-sm"
                placeholder="{{ __('Job, customer, inspector…') }}"
            >
        </div>
        <div>
            <label class="text-xs text-slate-600" for="status">{{ __('Status') }}</label>
            <select id="status" name="status" class="erp-select mt-1">
                <option value="">{{ __('All results') }}</option>
                <option value="pending" @selected($filters['status'] === 'pending')>{{ __('Pending inspection') }}</option>
                @foreach (App\Enums\QualityCheckResult::cases() as $result)
                    <option value="{{ $result->value }}" @selected($filters['status'] === $result->value)>
                        {{ $workspace->resultLabel($result) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs text-slate-600" for="date">{{ __('Date') }}</label>
            <input
                id="date"
                type="date"
                name="date"
                value="{{ $showingPending ? '' : $filters['date'] }}"
                class="erp-input mt-1 text-sm"
                @disabled($showingPending)
            >
        </div>
        @if ($inspectors->isNotEmpty())
            <div>
                <label class="text-xs text-slate-600" for="inspector">{{ __('Inspector') }}</label>
                <select id="inspector" name="inspector" class="erp-select mt-1" @disabled($showingPending)>
                    <option value="">{{ __('All inspectors') }}</option>
                    @foreach ($inspectors as $inspector)
                        <option value="{{ $inspector->id }}" @selected((string) $filters['inspector'] === (string) $inspector->id)>
                            {{ $inspector->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
        <x-secondary-button type="submit">{{ __('Filter') }}</x-secondary-button>
        @if ($filters['status'] || $filters['date'] || $filters['inspector'] || $filters['search'])
            <a href="{{ route('admin.production.quality.index') }}" class="text-sm text-slate-600 hover:text-erp-primary">{{ __('Clear') }}</a>
        @endif
    </form>

    <x-admin.card>
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Inspection Register') }}</h2>

        @if ($showingPending)
            @include('admin.production.quality.partials.register-pending', ['register' => $register])
        @else
            @include('admin.production.quality.partials.register', ['register' => $register, 'workspace' => $workspace])
        @endif
    </x-admin.card>
</x-admin-layout>

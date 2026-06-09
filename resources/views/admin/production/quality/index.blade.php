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

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.production.quality.index')" :reset-url="route('admin.production.quality.index')">
            <input id="search" type="search" name="search" value="{{ $filters['search'] }}" class="erp-toolbar-input min-w-[12rem] flex-1" placeholder="{{ __('Job, customer, inspector…') }}" aria-label="{{ __('Search') }}" data-erp-auto-search>
            <select id="status" name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="">{{ __('All results') }}</option>
                <option value="pending" @selected($filters['status'] === 'pending')>{{ __('Pending inspection') }}</option>
                @foreach (App\Enums\QualityCheckResult::cases() as $result)
                    <option value="{{ $result->value }}" @selected($filters['status'] === $result->value)>
                        {{ $workspace->resultLabel($result) }}
                    </option>
                @endforeach
            </select>
            <input id="date" type="date" name="date" value="{{ $showingPending ? '' : $filters['date'] }}" class="erp-toolbar-input" aria-label="{{ __('Date') }}" @disabled($showingPending)>
            @if ($inspectors->isNotEmpty())
                <select id="inspector" name="inspector" class="erp-toolbar-select" aria-label="{{ __('Inspector') }}" @disabled($showingPending)>
                    <option value="">{{ __('All inspectors') }}</option>
                    @foreach ($inspectors as $inspector)
                        <option value="{{ $inspector->id }}" @selected((string) $filters['inspector'] === (string) $inspector->id)>
                            {{ $inspector->name }}
                        </option>
                    @endforeach
                </select>
            @endif
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.card>
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Inspection Register') }}</h2>

        @if ($showingPending)
            @include('admin.production.quality.partials.register-pending', ['register' => $register, 'workspace' => $workspace])
        @else
            @include('admin.production.quality.partials.register', ['register' => $register, 'workspace' => $workspace])
        @endif
    </x-admin.card>
</x-admin-layout>

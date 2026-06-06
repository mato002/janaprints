@if ($tabData['type'] === 'machine')
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
        <x-admin.kpi-widget :label="__('Utilization')" :value="($tabData['utilization'] ?? 0).'%'" icon="chart-bar" />
        <x-admin.kpi-widget :label="__('Availability')" :value="$tabData['availability']['label'] ?? '—'" icon="check-circle" />
        <x-admin.kpi-widget :label="__('Assigned Jobs')" :value="$tabData['assigned_jobs']->count()" icon="clipboard-list" />
    </div>
@else
    <x-admin.card>
        <h3 class="mb-2 text-sm font-semibold">{{ __('Assignment Utilization') }}</h3>
        <p class="text-2xl font-semibold text-slate-500">{{ $tabData['assignment_utilization_label'] ?? __('Not yet available') }}</p>
        <p class="mt-1 text-sm text-slate-500">
            {{ ($tabData['is_assigned'] ?? false) ? __('Currently assigned') : __('Not assigned') }}
            @if (! empty($tabData['custody_status'])) — {{ $tabData['custody_status'] }} @endif
        </p>
    </x-admin.card>
@endif

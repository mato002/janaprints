@php
    $breadcrumbs = [
        ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
        ['label' => __('Inventory Control'), 'url' => route('admin.workspaces.supply-chain.section', ['section' => 'inventory-control'])],
        ['label' => __('Cycle Count'), 'url' => route('admin.inventory.cycle-counts.index')],
        ['label' => __('Schedule #').$schedule->id],
    ];
@endphp
<x-admin-layout :title="__('Cycle count schedule')" :breadcrumbs="$breadcrumbs">
    <x-admin.page-header :title="__('Schedule #').$schedule->id">
        @can('generate', $schedule)
            <form method="POST" action="{{ route('admin.inventory.cycle-counts.generate', $schedule) }}">@csrf<button class="erp-btn-primary">{{ __('Generate Count') }}</button></form>
        @endcan
    </x-admin.page-header>
    <x-admin.card>
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div><dt class="text-slate-500">{{ __('Warehouse') }}</dt><dd>{{ $schedule->warehouse?->name }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Frequency') }}</dt><dd>{{ ucfirst($schedule->frequency->value) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Next count') }}</dt><dd>{{ $schedule->next_count_date->format('Y-m-d') }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Responsible') }}</dt><dd>{{ $schedule->responsibleUser?->name }}</dd></div>
        </dl>
        @if ($schedule->stockCounts->isNotEmpty())
            <h3 class="font-medium mt-6 mb-2">{{ __('Generated counts') }}</h3>
            <ul class="text-sm">
                @foreach ($schedule->stockCounts as $count)
                    <li><a href="{{ route('admin.inventory.stock-counts.show', $count) }}" class="text-primary-600">{{ $count->count_number }}</a></li>
                @endforeach
            </ul>
        @endif
    </x-admin.card>
</x-admin-layout>

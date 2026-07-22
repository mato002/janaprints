@php
    $activeTab = $active_tab;
    $tabData = $tab_data;
@endphp

<x-admin-layout
    :title="$header['asset_number'].' — '.__('Asset 360')"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Asset Management'), 'url' => route('admin.assets.index')],
        ['label' => $header['asset_number'], 'url' => route('admin.assets.show', $asset)],
        ['label' => __('Asset 360')],
    ]"
>
    <x-admin.page-header :title="$header['asset_name']" :description="__('Asset 360 — :number', ['number' => $header['asset_number']])">
        <x-slot name="actions">
            <x-admin.status-badge :variant="$health['band']->badgeVariant()">{{ $health['band']->label() }} ({{ $health['score'] }})</x-admin.status-badge>
            <a href="{{ route('admin.assets.show', $asset) }}" class="erp-btn-secondary">{{ __('Asset Register') }}</a>
        </x-slot>
    </x-admin.page-header>

    <x-admin.card class="mb-4">
        <dl class="grid grid-cols-2 gap-3 text-sm md:grid-cols-4 lg:grid-cols-6">
            <div><dt class="text-slate-500">{{ __('Category') }}</dt><dd>{{ $header['category'] ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Status') }}</dt><dd>{{ $header['status']->label() }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Branch') }}</dt><dd>{{ $header['branch'] ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Custodian') }}</dt><dd>{{ $header['custodian'] ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Book Value') }}</dt><dd>{{ number_format($header['net_book_value'], 2) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Age') }}</dt><dd>{{ $header['age_years'] }} {{ __('yrs') }}</dd></div>
        </dl>
    </x-admin.card>

    <nav class="flex flex-wrap gap-2 border-b border-erp-border pb-2">
        @foreach ($tabs as $tab)
            <a href="{{ route('admin.assets.360.show', ['asset' => $asset, 'tab' => $tab['key']]) }}"
               class="rounded px-3 py-1.5 text-sm {{ $tab['active'] ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                {{ $tab['label'] }}
            </a>
        @endforeach
    </nav>

    <div class="mt-4">
        @include('admin.assets.360.tabs.'.$activeTab, ['tabData' => $tabData, 'asset' => $asset, 'health' => $health])
    </div>
</x-admin-layout>

@php
    $fromStoreDesk = (bool) ($fromStoreDesk ?? request('from') === 'store-desk');
    $breadcrumbs = $fromStoreDesk
        ? [
            ['label' => __('Store Desk'), 'url' => route('admin.store.desk')],
            ['label' => $issue->issue_number],
        ]
        : [
            ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
            ['label' => __('Store Management'), 'url' => route('admin.inventory.store.dashboard')],
            ['label' => __('Stock Issues'), 'url' => route('admin.inventory.issues.index')],
            ['label' => $issue->issue_number],
        ];
@endphp

<x-admin-layout :title="$issue->issue_number" :breadcrumbs="$breadcrumbs">
    <x-admin.page-header :title="$issue->issue_number">
        <span class="erp-badge">{{ $issue->status->value }}</span>
        @can('post', $issue)
            <form method="POST" action="{{ route('admin.inventory.issues.post', $issue) }}">
                @csrf
                @if ($fromStoreDesk)
                    <input type="hidden" name="from" value="store-desk">
                @endif
                <button class="erp-btn-primary">{{ __('Post issue') }}</button>
            </form>
        @endcan
        @if ($fromStoreDesk)
            <a href="{{ route('admin.store.desk') }}" class="erp-btn-secondary" data-turbo-frame="erp-main">{{ __('Back to Store Desk') }}</a>
        @endif
    </x-admin.page-header>
    <x-admin.card>
        @foreach ($issue->items as $line)
            <div class="py-1 text-sm">{{ $line->inventoryItem?->item_name }}: {{ $line->quantity }}</div>
        @endforeach
    </x-admin.card>
</x-admin-layout>

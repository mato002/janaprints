<x-admin-layout :title="$adjustment->adjustment_number" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Store Management'), 'url' => route('admin.inventory.store.dashboard')], ['label' => __('Adjustments'), 'url' => route('admin.inventory.adjustments.index')], ['label' => $adjustment->adjustment_number]]">
    <x-admin.page-header :title="$adjustment->adjustment_number">
        <span class="erp-badge">{{ $adjustment->status->value }}</span>
        @can('post', $adjustment)
            <form method="POST" action="{{ route('admin.inventory.adjustments.post', $adjustment) }}">@csrf
                <button class="erp-btn-primary">{{ __('Post adjustment') }}</button></form>
        @endcan
    </x-admin.page-header>
    <x-admin.card>
        <p class="text-sm text-slate-600 mb-2">{{ $adjustment->reason }}</p>
        @foreach ($adjustment->items as $line)
            <div class="text-sm py-1">{{ $line->inventoryItem?->item_name }}: {{ $line->direction->value }} {{ $line->quantity }}</div>
        @endforeach
    </x-admin.card>
</x-admin-layout>

@php
    $breadcrumbs = [
        ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
        ['label' => __('Inventory Control'), 'url' => route('admin.workspaces.supply-chain.section', ['section' => 'inventory-control'])],
        ['label' => __('Inventory Reconciliation'), 'url' => route('admin.inventory.reconciliations.index')],
        ['label' => $reconciliation->reconciliation_number],
    ];
    $count = $reconciliation->stockCount;
@endphp
<x-admin-layout :title="$reconciliation->reconciliation_number" :breadcrumbs="$breadcrumbs">
    <x-admin.page-header :title="$reconciliation->reconciliation_number">
        <x-admin.enum-status-badge :status="$reconciliation->status->value" />
        @can('approve', $reconciliation)
            <form method="POST" action="{{ route('admin.inventory.reconciliations.approve', $reconciliation) }}">@csrf<button class="erp-btn-primary">{{ __('Approve') }}</button></form>
        @endcan
        @can('post', $reconciliation)
            <form method="POST" action="{{ route('admin.inventory.reconciliations.post', $reconciliation) }}">@csrf<button class="erp-btn-primary">{{ __('Post') }}</button></form>
        @endcan
    </x-admin.page-header>

    <x-admin.card class="mb-6">
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div><dt class="text-slate-500">{{ __('Stock count') }}</dt><dd><a href="{{ route('admin.inventory.stock-counts.show', $count) }}" class="text-primary-600">{{ $count->count_number }}</a></dd></div>
            <div><dt class="text-slate-500">{{ __('Warehouse') }}</dt><dd>{{ $count->warehouse?->name }}</dd></div>
            @if ($reconciliation->stockAdjustment)
                <div><dt class="text-slate-500">{{ __('Adjustment ref') }}</dt><dd>{{ $reconciliation->stockAdjustment->adjustment_number }}</dd></div>
            @endif
        </dl>
        <h3 class="font-medium mt-6 mb-2">{{ __('Variance lines') }}</h3>
        @foreach ($count->items->where('variance_quantity', '!=', 0) as $line)
            <div class="text-sm py-1">{{ $line->inventoryItem?->item_name }}: {{ $line->variance_quantity }} ({{ number_format((float) $line->variance_value, 2) }})</div>
        @endforeach
    </x-admin.card>

    <x-admin.card id="audit">
        <h3 class="font-medium mb-3">{{ __('Audit history') }}</h3>
        @forelse ($auditHistory as $log)
            <div class="text-sm py-1 border-b border-slate-100">{{ $log->action }} · {{ $log->created_at?->format('Y-m-d H:i') }}</div>
        @empty
            <p class="text-sm text-slate-500">{{ __('No audit entries yet.') }}</p>
        @endforelse
    </x-admin.card>
</x-admin-layout>

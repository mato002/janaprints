@php
    $breadcrumbs = [
        ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
        ['label' => __('Inventory Control'), 'url' => route('admin.workspaces.supply-chain.section', ['section' => 'inventory-control'])],
        ['label' => __('Variance Report'), 'url' => route('admin.inventory.variances.index')],
        ['label' => __('PDF Export')],
    ];
@endphp
<x-admin-layout :title="__('PDF Export')" :breadcrumbs="$breadcrumbs">
    <x-admin.card>
        <p class="text-slate-600">{{ __('PDF export will be available when the document engine is configured.') }}</p>
        <a href="{{ route('admin.inventory.variances.index') }}" class="erp-btn-secondary mt-4 inline-block">{{ __('Back to report') }}</a>
    </x-admin.card>
</x-admin-layout>

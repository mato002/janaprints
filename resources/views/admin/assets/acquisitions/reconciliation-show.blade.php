<x-admin-layout :title="__('Reconciliation Detail')" :breadcrumbs="[['label' => __('Capitalization Reconciliation'), 'url' => route('admin.assets.acquisitions.reconciliation.index')], ['label' => $reconciliation->reconciliation_number]]">
    <x-admin.page-header :title="$reconciliation->reconciliation_number" :description="__('Capitalization reconciliation detail.')" />

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-admin.kpi-widget :label="__('Procurement Received')" :value="number_format($reconciliation->procurement_received_value, 2)" icon="truck" />
        <x-admin.kpi-widget :label="__('Capitalized')" :value="number_format($reconciliation->capitalized_value, 2)" icon="chip" />
        <x-admin.kpi-widget :label="__('Posted')" :value="number_format($reconciliation->posted_value, 2)" icon="document-text" />
        <x-admin.kpi-widget :label="__('Register')" :value="number_format($reconciliation->register_value, 2)" icon="clipboard-list" />
    </div>

    <x-admin.card class="mt-6">
        <h3 class="mb-3 text-sm font-semibold">{{ __('Variance Detection') }}</h3>
        <ul class="space-y-2 text-sm">
            <li>{{ __('Received not capitalized') }}: <strong>{{ $reconciliation->received_not_capitalized_count }}</strong></li>
            <li>{{ __('Capitalized not posted') }}: <strong>{{ $reconciliation->capitalized_not_posted_count }}</strong></li>
            <li>{{ __('Posted not registered') }}: <strong>{{ $reconciliation->posted_not_registered_count }}</strong></li>
        </ul>
        @if (! empty($reconciliation->variance_details))
            <div class="mt-4 rounded border border-erp-border p-3 text-sm">
                <pre class="whitespace-pre-wrap">{{ json_encode($reconciliation->variance_details, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif
    </x-admin.card>
</x-admin-layout>

<x-admin-layout :title="$reconciliation->reconciliation_no" :breadcrumbs="[['label' => __('Reconciliation'), 'url' => route('admin.assets.finance.reconciliation.index')], ['label' => $reconciliation->reconciliation_no]]">
    <x-admin.page-header :title="$reconciliation->reconciliation_no">
        <x-slot name="actions"><x-admin.status-badge :variant="$reconciliation->status->badgeVariant()">{{ $reconciliation->status->label() }}</x-admin.status-badge></x-slot>
    </x-admin.page-header>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <x-admin.card>
            <h3 class="mb-3 font-semibold">{{ __('Asset Register') }}</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt>{{ __('Cost') }}</dt><dd>{{ number_format($reconciliation->register_cost, 2) }}</dd></div>
                <div class="flex justify-between"><dt>{{ __('Accumulated') }}</dt><dd>{{ number_format($reconciliation->register_accumulated, 2) }}</dd></div>
                <div class="flex justify-between"><dt>{{ __('NBV') }}</dt><dd>{{ number_format($reconciliation->register_nbv, 2) }}</dd></div>
            </dl>
        </x-admin.card>
        <x-admin.card>
            <h3 class="mb-3 font-semibold">{{ __('General Ledger') }}</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt>{{ __('Cost') }}</dt><dd>{{ number_format($reconciliation->gl_cost, 2) }}</dd></div>
                <div class="flex justify-between"><dt>{{ __('Accumulated') }}</dt><dd>{{ number_format($reconciliation->gl_accumulated, 2) }}</dd></div>
                <div class="flex justify-between"><dt>{{ __('NBV') }}</dt><dd>{{ number_format($reconciliation->gl_nbv, 2) }}</dd></div>
            </dl>
        </x-admin.card>
    </div>
    @if ($reconciliation->findings)
        <x-admin.card class="mt-4">
            <h3 class="mb-3 font-semibold">{{ __('Findings') }}</h3>
            <ul class="space-y-2 text-sm">
                @foreach ($reconciliation->findings as $finding)
                    <li>{{ $finding['message'] ?? '' }}</li>
                @endforeach
            </ul>
        </x-admin.card>
    @endif
</x-admin-layout>

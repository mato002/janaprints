<x-admin-layout :title="__('POS Returns')" :breadcrumbs="[['label' => __('POS'), 'url' => route('admin.commercial.pos.dashboard')], ['label' => __('Returns')]]">
    <x-admin.page-header :title="__('Returns Dashboard')" :description="__('Process retail sale corrections via reversal returns.')">
        <x-slot name="actions">
            @can('create', App\Models\Pos\PosReturn::class)
                <a href="{{ route('admin.commercial.pos.returns.create') }}" class="erp-btn-primary">{{ __('Create Return') }}</a>
            @endcan
            <a href="{{ route('admin.commercial.pos.returns.index') }}" class="erp-btn-secondary">{{ __('Return History') }}</a>
        </x-slot>
    </x-admin.page-header>

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-admin.kpi-widget :label="__('Pending')" :value="$stats['pending']" icon="clock" />
        <x-admin.kpi-widget :label="__('Completed Today')" :value="$stats['completed_today']" icon="check-circle" />
        <x-admin.kpi-widget :label="__('Refunds Today')" :value="number_format($stats['refund_today'], 2)" icon="currency-dollar" />
        <x-admin.kpi-widget :label="__('Rejected')" :value="$stats['rejected']" icon="x-circle" />
    </div>

    <x-admin.card>
        <h3 class="mb-3 font-medium">{{ __('Recent Returns') }}</h3>
        <ul class="space-y-2 text-sm">
            @forelse ($recent as $return)
                <li class="flex items-center justify-between gap-2 border-b border-erp-border py-2">
                    <div>
                        <a href="{{ route('admin.commercial.pos.returns.show', $return) }}" class="font-medium text-erp-accent">{{ $return->return_number }}</a>
                        <span class="text-slate-500"> — {{ $return->sale?->sale_number }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-admin.enum-status-badge :status="$return->status->value" />
                        <span class="tabular-nums">{{ number_format($return->refund_amount, 2) }}</span>
                    </div>
                </li>
            @empty
                <li class="text-slate-500">{{ __('No returns yet.') }}</li>
            @endforelse
        </ul>
    </x-admin.card>
</x-admin-layout>

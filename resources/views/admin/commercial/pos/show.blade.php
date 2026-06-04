<x-admin-layout :title="$sale->sale_number" :breadcrumbs="[['label' => __('POS'), 'url' => route('admin.commercial.pos.dashboard')], ['label' => $sale->sale_number]]">
    <x-admin.page-header :title="$sale->sale_number" :description="$sale->sale_date->format('Y-m-d')">
        <x-slot name="actions">
            @if ($sale->status === App\Enums\PosSaleStatus::Paid)
                <a href="{{ route('admin.commercial.pos.receipt', $sale) }}" class="erp-btn-primary">{{ __('Receipt') }}</a>
            @endif
            @if ($sale->customer)
                <x-admin.customer-360-action :customer="$sale->customer" />
            @endif
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <x-admin.card>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-slate-500">{{ __('Status') }}</dt><dd><x-admin.enum-status-badge :status="$sale->status->value" /></dd></div>
                <div><dt class="text-slate-500">{{ __('Cashier') }}</dt><dd>{{ $sale->cashier?->name }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Customer') }}</dt><dd>{{ $sale->is_walk_in ? __('Walk-in') : ($sale->customer?->company_name ?? '—') }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Total') }}</dt><dd class="font-semibold tabular-nums">{{ number_format($sale->total_amount, 2) }}</dd></div>
            </dl>
            @can('cancel', $sale)
                @if (! in_array($sale->status, [App\Enums\PosSaleStatus::Cancelled, App\Enums\PosSaleStatus::Refunded], true))
                    <form method="POST" action="{{ route('admin.commercial.pos.cancel', $sale) }}" class="mt-4">@csrf<button class="text-sm text-red-600">{{ __('Cancel sale') }}</button></form>
                @endif
            @endcan
            @can('refund', $sale)
                @if ($sale->status === App\Enums\PosSaleStatus::Paid)
                    <form method="POST" action="{{ route('admin.commercial.pos.refund', $sale) }}" class="mt-2">@csrf<button class="text-sm text-amber-700">{{ __('Mark refunded') }}</button></form>
                @endif
            @endcan
        </x-admin.card>
        <x-admin.card>
            <h3 class="mb-2 font-medium">{{ __('Line items') }}</h3>
            <ul class="text-sm space-y-2">
                @foreach ($sale->items as $item)
                    <li class="flex justify-between border-b border-erp-border py-1">
                        <span>{{ $item->description }} × {{ $item->quantity }}</span>
                        <span class="tabular-nums">{{ number_format($item->line_total, 2) }}</span>
                    </li>
                @endforeach
            </ul>
        </x-admin.card>
    </div>
</x-admin-layout>

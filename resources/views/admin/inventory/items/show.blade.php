<x-admin-layout :title="$item->sku">
    <x-admin.page-header :title="$item->item_name" :description="$item->sku">
        <span class="erp-badge">{{ __('Stock') }}: {{ number_format($stockBalance, 3) }}</span>
        @can('update', $item)<a href="{{ route('admin.inventory.items.edit', $item) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>@endcan
    </x-admin.page-header>
    <x-admin.card>
        <dl class="text-sm space-y-2">
            <div><dt class="text-slate-500">{{ __('Category') }}</dt><dd>{{ $item->category?->name }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Unit') }}</dt><dd>{{ $item->unitOfMeasure?->name }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Reorder level') }}</dt><dd>{{ $item->reorder_level }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Standard cost') }}</dt><dd>{{ number_format($item->standard_cost, 2) }}</dd></div>
        </dl>
        <p class="text-xs text-slate-500 mt-4">{{ __('Balance is calculated from inventory movements only.') }}</p>
    </x-admin.card>
</x-admin-layout>

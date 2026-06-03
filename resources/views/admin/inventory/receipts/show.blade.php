<x-admin-layout :title="$receipt->receipt_number">
    <x-admin.page-header :title="$receipt->receipt_number">
        <span class="erp-badge">{{ $receipt->status->value }}</span>
        @can('post', $receipt)
            <form method="POST" action="{{ route('admin.inventory.receipts.post', $receipt) }}">@csrf
                <button class="erp-btn-primary">{{ __('Post receipt') }}</button></form>
        @endcan
    </x-admin.page-header>
    <x-admin.card>
        <p class="text-sm mb-4">{{ $receipt->warehouse?->name }} — {{ $receipt->receipt_date->format('Y-m-d') }}</p>
        @foreach ($receipt->items as $line)
            <div class="text-sm py-1">{{ $line->inventoryItem?->item_name }}: {{ $line->quantity }} @ {{ $line->unit_cost }}</div>
        @endforeach
    </x-admin.card>
</x-admin-layout>

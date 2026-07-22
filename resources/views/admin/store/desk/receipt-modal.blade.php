<x-admin.modal-form :title="$receipt->receipt_number" maxWidth="4xl">
    <div class="space-y-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="erp-badge">{{ $receipt->status->value }}</span>
            <span class="text-sm text-slate-600">{{ $receipt->warehouse?->name }} · {{ $receipt->receipt_date->format('d M Y') }}</span>
        </div>

        <div class="rounded-lg border border-erp-border bg-white p-4">
            <h3 class="mb-2 text-sm font-semibold text-slate-900">{{ __('Line items') }}</h3>
            @foreach ($receipt->items as $line)
                <div class="border-b border-slate-100 py-2 text-sm last:border-0">
                    <span class="font-medium">{{ $line->inventoryItem?->item_name ?? '—' }}</span>
                    <span class="text-slate-500"> — {{ number_format((float) $line->quantity, 2) }} @ {{ number_format((float) $line->unit_cost, 2) }}</span>
                </div>
            @endforeach
        </div>

        @can('post', $receipt)
            <form method="POST" action="{{ route('admin.inventory.receipts.post', $receipt) }}">
                @csrf
                <input type="hidden" name="from" value="store-desk">
                <button type="submit" class="erp-btn-primary text-sm">{{ __('Post to stock') }}</button>
            </form>
        @endcan
    </div>
</x-admin.modal-form>

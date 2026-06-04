<a href="{{ route('admin.sales-orders.show', $item) }}" class="flex items-center justify-between gap-2 border-b border-erp-border py-2 text-sm last:border-0 hover:bg-erp-page/50" data-turbo-frame="erp-main">
    <span class="font-medium text-erp-primary">{{ $item->order_number }}</span>
    <x-admin.enum-status-badge :status="$item->status->value" />
    <span class="tabular-nums text-slate-600">{{ number_format($item->total_amount, 2) }}</span>
</a>

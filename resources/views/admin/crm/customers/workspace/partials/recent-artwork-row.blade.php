<a href="{{ route('admin.artwork.show', $item) }}" class="flex items-center justify-between gap-2 border-b border-erp-border py-2 text-sm last:border-0 hover:bg-erp-page/50" data-turbo-frame="erp-main">
    <span class="font-medium text-erp-primary">{{ $item->request_number }}</span>
    <x-admin.enum-status-badge :status="$item->status->value" />
    <span class="text-xs text-slate-500">v{{ $item->current_version }}</span>
</a>

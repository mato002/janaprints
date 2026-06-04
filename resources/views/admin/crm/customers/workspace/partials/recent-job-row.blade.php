<a href="{{ route('admin.production.job-cards.show', $item) }}" class="flex items-center justify-between gap-2 border-b border-erp-border py-2 text-sm last:border-0 hover:bg-erp-page/50" data-turbo-frame="erp-main">
    <span class="font-medium text-erp-primary">{{ $item->job_card_number }}</span>
    <x-admin.enum-status-badge :status="$item->status->value" />
</a>

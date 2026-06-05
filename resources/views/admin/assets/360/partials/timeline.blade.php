<ul class="space-y-2 text-sm">
    @forelse ($entries as $entry)
        <li class="flex justify-between border-b border-erp-border pb-2">
            <span>{{ $entry->title ?? $entry['title'] ?? '' }}</span>
            <span class="text-slate-500">{{ ($entry->occurred_at ?? $entry['occurred_at'] ?? null)?->format('Y-m-d H:i') }}</span>
        </li>
    @empty
        <li class="text-slate-500">{{ __('No timeline entries.') }}</li>
    @endforelse
</ul>

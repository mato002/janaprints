<x-admin.card class="mb-4">
    <h3 class="mb-2 text-sm font-semibold">{{ __('Lifecycle Progress') }}</h3>
    <div class="h-3 w-full rounded bg-slate-200">
        <div class="h-3 rounded bg-slate-800" style="width: {{ min(100, $tabData['lifecycle_progress']) }}%"></div>
    </div>
    <p class="mt-1 text-xs text-slate-500">{{ $tabData['lifecycle_progress'] }}% {{ __('of useful life elapsed') }}</p>
</x-admin.card>
<x-admin.card>
    <h3 class="mb-3 text-sm font-semibold">{{ __('Unified Timeline') }}</h3>
    <ul class="space-y-2 text-sm">
        @foreach ($tabData['timeline'] as $entry)
            <li class="flex justify-between border-b border-erp-border pb-2">
                <span><span class="text-xs uppercase text-slate-400">{{ $entry['domain'] }}</span> {{ $entry['title'] }}</span>
                <span class="text-slate-500">{{ $entry['occurred_at']?->format('Y-m-d H:i') }}</span>
            </li>
        @endforeach
    </ul>
</x-admin.card>

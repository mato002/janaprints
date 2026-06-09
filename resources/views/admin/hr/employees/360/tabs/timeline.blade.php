<x-admin.card>
    <h3 class="mb-4 font-semibold text-erp-primary">{{ __('Employee Timeline') }}</h3>
    <ol class="relative border-s border-slate-200 ms-3 space-y-6">
        @forelse ($timeline as $event)
            <li class="ms-6">
                <span class="absolute -start-1.5 mt-1.5 h-3 w-3 rounded-full bg-erp-primary"></span>
                <div class="text-sm">
                    <time class="text-xs text-slate-500">{{ $event->eventDatetime->format('M j, Y H:i') }}</time>
                    <p class="font-medium text-erp-primary">{{ $event->title }}</p>
                    @if ($event->description)
                        <p class="text-slate-600">{{ $event->description }}</p>
                    @endif
                    @if ($event->actorName)
                        <p class="text-xs text-slate-500">{{ __('By') }} {{ $event->actorName }}</p>
                    @endif
                    <span class="mt-1 inline-block rounded bg-slate-100 px-2 py-0.5 text-[10px] uppercase tracking-wide text-slate-600">{{ $event->category }}</span>
                </div>
            </li>
        @empty
            <li class="ms-6 text-sm text-slate-500">{{ __('No timeline events yet.') }}</li>
        @endforelse
    </ol>
</x-admin.card>

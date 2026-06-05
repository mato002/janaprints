@props(['events'])

@php
    $dotClasses = [
        'slate' => 'bg-slate-400 ring-slate-400/10',
        'blue' => 'bg-blue-500 ring-blue-500/10',
        'indigo' => 'bg-indigo-500 ring-indigo-500/10',
        'emerald' => 'bg-emerald-500 ring-emerald-500/10',
        'red' => 'bg-red-500 ring-red-500/10',
        'amber' => 'bg-amber-500 ring-amber-500/10',
        'sky' => 'bg-sky-500 ring-sky-500/10',
        'violet' => 'bg-violet-500 ring-violet-500/10',
        'pink' => 'bg-pink-500 ring-pink-500/10',
        'orange' => 'bg-orange-500 ring-orange-500/10',
        'rose' => 'bg-rose-500 ring-rose-500/10',
    ];

    $categoryLabels = [
        'notes' => __('Notes'),
        'activities' => __('Activities'),
        'files' => __('Files'),
        'quotations' => __('Quotations'),
        'orders' => __('Orders'),
        'artwork' => __('Artwork'),
        'production' => __('Production'),
        'quality' => __('Quality'),
        'dispatch' => __('Dispatch'),
        'accounting' => __('Accounting'),
        'operations' => __('Operations'),
        'materials' => __('Materials'),
        'traceability' => __('Traceability'),
    ];
@endphp

<ul class="c360-timeline-feed space-y-0" role="list">
    @forelse ($events as $event)
        @php
            $title = is_array($event) ? ($event['title'] ?? '') : $event->title;
            $description = is_array($event) ? ($event['description'] ?? null) : $event->description;
            $eventDatetime = is_array($event)
                ? \Illuminate\Support\Carbon::parse($event['event_datetime'] ?? now())
                : $event->eventDatetime;
            $actorName = is_array($event) ? ($event['actor_name'] ?? null) : $event->actorName;
            $sourceUrl = is_array($event) ? ($event['source_url'] ?? null) : $event->sourceUrl;
            $color = is_array($event) ? ($event['color'] ?? 'slate') : $event->color;
            $category = is_array($event) ? ($event['category'] ?? 'all') : $event->category;
            $dotClass = $dotClasses[$color] ?? $dotClasses['slate'];
            $categoryLabel = $categoryLabels[$category] ?? str($category)->replace('_', ' ')->title();
        @endphp
        <li class="relative flex gap-4 py-4 pl-6">
            <span class="absolute left-0 top-4 flex h-3 w-3 items-center justify-center">
                <span class="h-2 w-2 rounded-full ring-4 {{ $dotClass }}"></span>
            </span>
            @if (! $loop->last)
                <span class="absolute left-[5px] top-5 h-full w-px bg-erp-border" aria-hidden="true"></span>
            @endif
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div class="min-w-0">
                        @if ($sourceUrl)
                            <a href="{{ $sourceUrl }}" data-turbo-frame="erp-main" class="text-sm font-semibold text-erp-primary hover:text-erp-accent">
                                {{ $title }}
                            </a>
                        @else
                            <p class="text-sm font-semibold text-erp-primary">{{ $title }}</p>
                        @endif
                        @if ($description)
                            <p class="mt-1 text-sm text-slate-600 line-clamp-2">{{ $description }}</p>
                        @endif
                    </div>
                    @if ($category !== 'all')
                        <span class="shrink-0 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-600">
                            {{ $categoryLabel }}
                        </span>
                    @endif
                </div>
                <p class="mt-1 text-[10px] text-slate-400">
                    @if ($actorName)
                        {{ $actorName }}
                        ·
                    @endif
                    <time datetime="{{ $eventDatetime->toIso8601String() }}">{{ $eventDatetime->diffForHumans() }}</time>
                    ·
                    {{ $eventDatetime->format('M j, Y H:i') }}
                </p>
            </div>
        </li>
    @empty
        <li class="py-8 text-center text-sm text-slate-500">{{ __('No timeline events yet.') }}</li>
    @endforelse
</ul>

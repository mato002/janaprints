@php($cards = $cards ?? [])

<section class="job-360-overview-cards mb-6" aria-label="{{ __('Job overview') }}">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach ($cards as $card)
            <article class="job-360-overview-card rounded-lg border border-erp-border bg-erp-card p-3 shadow-card">
                <span class="job-360-overview-card__label text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                    {{ $card['label'] }}
                </span>
                @if (! empty($card['url']))
                    <a
                        href="{{ $card['url'] }}"
                        class="job-360-overview-card__value mt-1 block text-sm font-semibold text-erp-accent hover:text-erp-accent-hover"
                        data-turbo-frame="erp-main"
                    >{{ $card['value'] }}</a>
                @else
                    <p class="job-360-overview-card__value mt-1 text-sm font-semibold text-erp-primary">{{ $card['value'] }}</p>
                @endif
                @if (! empty($card['hint']))
                    <p class="mt-1 text-xs text-slate-500">{{ $card['hint'] }}</p>
                @endif
            </article>
        @endforeach
    </div>
</section>

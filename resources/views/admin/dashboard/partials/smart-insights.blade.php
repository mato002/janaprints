<section class="exec-panel exec-panel--insights" aria-label="{{ __('Smart insights') }}">
    <div class="exec-panel__head"><h2 class="exec-panel__title">{{ __('Smart Insights') }}</h2></div>
    <ul class="space-y-1.5">
        @foreach ($dashboard['insights'] as $insight)
            @php
                $tone = match ($insight['tone']) {
                    'success' => 'text-emerald-700',
                    'danger' => 'text-red-700',
                    'warning' => 'text-amber-700',
                    'info' => 'text-sky-700',
                    default => 'text-slate-600',
                };
            @endphp
            <li class="exec-insight {{ $tone }}">{{ $insight['text'] }}</li>
        @endforeach
    </ul>
</section>

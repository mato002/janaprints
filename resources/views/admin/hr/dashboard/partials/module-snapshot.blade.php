@props(['snapshot'])

@php
    use App\Support\Navigation\WorkspaceEmbed;
    $turboFrame = WorkspaceEmbed::turboFrame();
@endphp

<x-admin.card>
    <div class="mb-3 flex items-start justify-between gap-2">
        <h2 class="text-xs font-semibold uppercase tracking-wide text-erp-primary">{{ $snapshot['title'] }}</h2>
        @if ($snapshot['open_url'] ?? null)
            <a href="{{ \App\Support\Navigation\WorkspaceEmbed::url($snapshot['open_url']) }}" class="shrink-0 text-[11px] font-medium text-erp-accent hover:underline" data-turbo-frame="{{ $turboFrame }}">
                {{ $snapshot['open_label'] }}
            </a>
        @endif
    </div>

    <dl class="grid grid-cols-2 gap-x-3 gap-y-2 text-xs">
        @foreach ($snapshot['metrics'] as $metric)
            <div>
                <dt class="text-[10px] uppercase tracking-wide text-slate-400">{{ $metric['label'] }}</dt>
                <dd class="mt-0.5 font-semibold tabular-nums text-erp-primary">{{ $metric['value'] }}</dd>
            </div>
        @endforeach
    </dl>

    @if (! empty($snapshot['trend']))
        <div class="mt-3 border-t border-erp-border pt-3">
            <p class="mb-2 text-[10px] uppercase tracking-wide text-slate-400">{{ __('Attendance Trend (7 days)') }}</p>
            <div class="exec-bar-chart exec-bar-chart--tall" role="img" aria-label="{{ __('Attendance trend') }}">
                @foreach ($snapshot['trend'] as $point)
                    <div class="exec-bar-chart__col" title="{{ $point['label'] }}: {{ number_format($point['value'], 1) }}%">
                        <div class="exec-bar-chart__bar" style="height: {{ max($point['percent'], 4) }}%"></div>
                        <span class="exec-bar-chart__label">{{ $point['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</x-admin.card>

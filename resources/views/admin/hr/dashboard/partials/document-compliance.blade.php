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

    @if (! empty($snapshot['categories']))
        <div class="mt-3 border-t border-erp-border pt-3">
            <p class="mb-2 text-[10px] uppercase tracking-wide text-slate-400">{{ __('By Category') }}</p>
            <ul class="space-y-1 text-xs">
                @foreach ($snapshot['categories'] as $category)
                    <li class="flex items-center justify-between gap-2 rounded border border-erp-border/60 px-2 py-1.5">
                        <span class="text-slate-600">{{ $category['label'] }}</span>
                        <span class="tabular-nums text-slate-500">
                            @if ($category['expiring'] > 0)
                                <span class="text-amber-700">{{ $category['expiring'] }} {{ __('expiring') }}</span>
                            @endif
                            @if ($category['expired'] > 0)
                                <span class="text-red-700">{{ $category['expired'] }} {{ __('expired') }}</span>
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</x-admin.card>

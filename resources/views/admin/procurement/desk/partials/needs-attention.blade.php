@php
    use App\Support\Navigation\WorkspaceEmbed;

    $needsAttention = $needsAttention ?? [];
    $frame = WorkspaceEmbed::turboFrame();
@endphp

<section class="store-desk-attention rounded-xl border border-erp-border bg-white shadow-sm" aria-label="{{ __('Needs attention') }}">
    <div class="flex items-center justify-between gap-2 border-b border-erp-border px-3 py-2">
        <h2 class="text-sm font-semibold text-slate-900">{{ __('Needs attention') }}</h2>
    </div>

    <ul class="divide-y divide-slate-100">
        @forelse ($needsAttention as $item)
            @php
                $dot = match ($item['severity'] ?? 'warning') {
                    'critical' => 'bg-rose-500',
                    'ok' => 'bg-emerald-500',
                    default => 'bg-amber-400',
                };
            @endphp
            <li>
                @if (! empty($item['url']))
                    <a
                        href="{{ WorkspaceEmbed::url($item['url']) }}"
                        class="flex items-center justify-between gap-3 px-3 py-2 text-sm transition hover:bg-slate-50"
                        data-turbo-frame="{{ $frame }}"
                        data-turbo-action="advance"
                    >
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <span class="h-2 w-2 shrink-0 rounded-full {{ $dot }}" aria-hidden="true"></span>
                            <span class="truncate font-medium text-slate-900">{{ $item['label'] }}</span>
                        </span>
                        @if (($item['count'] ?? 0) > 0)
                            <span class="shrink-0 tabular-nums text-xs font-semibold text-slate-600">{{ $item['count'] }}</span>
                        @endif
                    </a>
                @else
                    <div class="flex items-center gap-2 px-3 py-2 text-sm text-slate-600">
                        <span class="h-2 w-2 shrink-0 rounded-full {{ $dot }}" aria-hidden="true"></span>
                        <span>{{ $item['label'] }}</span>
                    </div>
                @endif
            </li>
        @empty
            <li class="px-3 py-3 text-sm text-slate-500">{{ __('Nothing requires attention right now.') }}</li>
        @endforelse
    </ul>
</section>

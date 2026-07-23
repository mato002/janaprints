@php
    use App\Support\Navigation\WorkspaceEmbed;

    $needsAttention = $needsAttention ?? [];
    $lowStockItems = $lowStockItems ?? [];
    $frame = WorkspaceEmbed::turboFrame();
@endphp

<section class="store-desk-attention rounded-xl border border-erp-border bg-white shadow-sm" aria-label="{{ __('Needs attention') }}">
    <div class="flex items-center justify-between gap-2 border-b border-erp-border px-3 py-2">
        <h2 class="text-sm font-semibold text-slate-900">{{ __('Needs attention') }}</h2>
        @if (count($lowStockItems) > 0)
            <a href="{{ $reorderAlertsUrl ?? route('admin.store.desk.reorder-alerts') }}" class="text-[11px] font-semibold text-erp-accent hover:underline" data-erp-modal-open>{{ __('All alerts') }}</a>
        @endif
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
                        href="{{ ($item['modal'] ?? false) ? $item['url'] : WorkspaceEmbed::url($item['url']) }}"
                        class="flex items-center justify-between gap-3 px-3 py-2 text-sm transition hover:bg-slate-50"
                        @if ($item['modal'] ?? false)
                            data-erp-modal-open
                        @else
                            data-turbo-frame="{{ $frame }}"
                            data-turbo-action="advance"
                        @endif
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

    @if (count($lowStockItems) > 0)
        <div class="border-t border-erp-border bg-slate-50/50 px-3 py-2">
            <p class="mb-1.5 text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __('Low stock') }}</p>
            <ul class="space-y-1.5">
                @foreach ($lowStockItems as $item)
                    <li class="flex items-center justify-between gap-2 text-xs">
                        <span class="min-w-0 truncate">
                            <span class="font-medium text-slate-900">{{ $item['name'] }}</span>
                            <span @class([
                                'ml-1 tabular-nums',
                                'text-rose-700' => $item['urgent'] ?? false,
                                'text-amber-700' => ! ($item['urgent'] ?? false),
                            ])>{{ $item['remaining_label'] }}</span>
                        </span>
                        <a href="{{ $item['url'] ?? $reorderAlertsUrl }}" class="shrink-0 font-semibold text-erp-accent hover:underline" data-erp-modal-open>{{ $item['action'] ?? __('Purchase') }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</section>

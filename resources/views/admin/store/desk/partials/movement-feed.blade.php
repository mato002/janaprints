@php
    use App\Support\Navigation\WorkspaceEmbed;
@endphp

<section class="rounded-xl border border-erp-border bg-white shadow-sm" aria-label="{{ __('Inventory activity') }}">
    <div class="flex items-center justify-between gap-2 border-b border-erp-border px-3 py-2">
        <h2 class="text-sm font-semibold text-slate-900">{{ __('Inventory activity') }}</h2>
        <a
            href="{{ WorkspaceEmbed::url(\App\Support\Inventory\StoreDeskViews::deskUrl(\App\Support\Inventory\StoreDeskViews::MOVEMENTS)) }}"
            class="text-[11px] font-semibold text-erp-accent hover:underline"
            data-turbo-frame="{{ WorkspaceEmbed::turboFrame() }}"
            data-turbo-action="advance"
        >{{ __('All movements') }}</a>
    </div>
    @if (count($movementFeed ?? []) === 0)
        <div class="px-3 py-5 text-center text-sm text-slate-500">{{ __('No stock movements recorded yet.') }}</div>
    @else
        <ul class="divide-y divide-slate-100">
            @foreach ($movementFeed as $movement)
                <li class="flex items-center gap-3 px-3 py-2 text-sm">
                    <span class="w-10 shrink-0 font-mono text-[11px] tabular-nums text-slate-500">{{ $movement['time'] }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate font-medium text-slate-900">{{ $movement['type'] }} · {{ $movement['item'] }}</span>
                        @if (! empty($movement['warehouse']))
                            <span class="block truncate text-[11px] text-slate-500">{{ $movement['warehouse'] }}</span>
                        @endif
                    </span>
                    <span @class([
                        'shrink-0 font-mono text-xs font-semibold tabular-nums',
                        'text-emerald-700' => $movement['inbound'],
                        'text-rose-700' => ! $movement['inbound'],
                    ])>{{ $movement['quantity'] }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</section>

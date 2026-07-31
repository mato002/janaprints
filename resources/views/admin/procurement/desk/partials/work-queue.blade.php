@php
    use App\Support\Navigation\WorkspaceEmbed;

    $frame = WorkspaceEmbed::turboFrame();
    $queueItems = $queueItems ?? [];
@endphp

<section class="rounded-xl border border-erp-border bg-white shadow-sm" aria-label="{{ __('Work queue') }}">
    <div class="flex items-center justify-between gap-2 border-b border-erp-border px-3 py-2">
        <h2 class="text-sm font-semibold text-slate-900">{{ __('Work queue') }}</h2>
        <a
            href="{{ WorkspaceEmbed::url(\App\Support\Procurement\ProcurementDeskViews::deskUrl(\App\Support\Procurement\ProcurementDeskViews::REQUESTS)) }}"
            class="text-[11px] font-semibold text-erp-accent hover:underline"
            data-turbo-frame="{{ $frame }}"
            data-turbo-action="advance"
        >{{ __('All requests') }}</a>
    </div>

    @if (count($queueItems) === 0)
        <div class="px-3 py-5 text-center text-sm text-slate-500">{{ __('No open buy work right now.') }}</div>
    @else
        <ul class="divide-y divide-slate-100">
            @foreach ($queueItems as $item)
                <li>
                    <a
                        href="{{ WorkspaceEmbed::url($item['url']) }}"
                        class="flex items-start justify-between gap-3 px-3 py-2.5 text-sm transition hover:bg-slate-50"
                        data-turbo-frame="{{ $frame }}"
                        data-turbo-action="advance"
                    >
                        <span class="min-w-0">
                            <span class="block truncate font-medium text-slate-900">{{ $item['title'] }}</span>
                            <span class="mt-0.5 block truncate text-xs text-slate-500">
                                <span class="font-mono">{{ $item['label'] }}</span>
                                <span class="mx-1 text-slate-300">·</span>
                                {{ $item['meta'] }}
                            </span>
                        </span>
                        <span @class([
                            'shrink-0 text-[11px] font-semibold',
                            'text-rose-700' => ($item['tone'] ?? '') === 'rose',
                            'text-amber-700' => ($item['tone'] ?? '') === 'amber',
                            'text-blue-700' => ($item['tone'] ?? '') === 'blue',
                            'text-emerald-700' => ($item['tone'] ?? '') === 'emerald',
                            'text-slate-600' => ! in_array($item['tone'] ?? '', ['rose', 'amber', 'blue', 'emerald'], true),
                        ])>{{ $item['status'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</section>

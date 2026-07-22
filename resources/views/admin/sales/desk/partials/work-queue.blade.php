@if (count($workQueue['items'] ?? []) > 0)
    <x-admin.card class="mb-4">
        <h2 class="mb-2 text-sm font-semibold text-slate-900">{{ __('Needs attention') }}</h2>
        <ul class="divide-y divide-slate-100">
            @foreach ($workQueue['items'] as $item)
                @php
                    $toneClasses = match ($item['tone'] ?? 'slate') {
                        'amber' => 'border-amber-200 bg-amber-50 text-amber-900',
                        'indigo' => 'border-indigo-200 bg-indigo-50 text-indigo-900',
                        'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
                        'rose' => 'border-rose-200 bg-rose-50 text-rose-900',
                        default => 'border-slate-200 bg-slate-50 text-slate-800',
                    };
                    $kindLabel = match ($item['kind'] ?? '') {
                        'quote_request' => __('Lead'),
                        'quotation' => __('Quote'),
                        'release' => __('Release'),
                        'draft_quote' => __('Draft'),
                        'follow_up' => __('Follow-up'),
                        default => __('Task'),
                    };
                @endphp
                <li>
                    <a
                        href="{{ $item['url'] }}"
                        @class(['flex items-center justify-between gap-3 px-1 py-2.5 text-sm transition hover:bg-slate-50'])
                        @if ($item['modal'] ?? false) data-erp-modal-open @else data-turbo-frame="_top" @endif
                    >
                        <span class="min-w-0">
                            <span class="mb-0.5 inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide {{ $toneClasses }}">{{ $kindLabel }}</span>
                            <span class="block truncate font-medium text-slate-900">{{ $item['label'] }}</span>
                            @if (! empty($item['meta']))
                                <span class="block truncate text-xs text-slate-500">{{ $item['meta'] }}</span>
                            @endif
                        </span>
                        <span class="shrink-0 text-xs font-medium text-erp-accent">{{ __('Open') }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </x-admin.card>
@endif

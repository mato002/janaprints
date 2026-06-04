@props(['items', 'title' => null])

<x-admin.card class="mb-6">
    <h2 class="mb-3 text-sm font-semibold text-erp-primary">{{ $title ?? __('Attention Center') }}</h2>
    <ul class="divide-y divide-erp-border" role="list">
        @forelse ($items as $item)
            <li class="flex flex-wrap items-center justify-between gap-2 py-2.5 text-sm">
                <span class="font-medium text-erp-primary">{{ $item['label'] }}</span>
                <span @class([
                    'inline-flex min-w-[2rem] items-center justify-center rounded-full px-2 py-0.5 text-xs font-bold tabular-nums',
                    'bg-red-100 text-red-700' => ($item['severity'] ?? '') === 'danger',
                    'bg-amber-100 text-amber-800' => ($item['severity'] ?? '') === 'warning',
                    'bg-slate-100 text-slate-600' => ($item['severity'] ?? '') === 'muted',
                ])>
                    {{ $item['display'] ?? $item['count'] ?? '—' }}
                </span>
            </li>
        @empty
            <li class="py-4 text-center text-sm text-slate-500">{{ __('No alerts for current filters.') }}</li>
        @endforelse
    </ul>
</x-admin.card>

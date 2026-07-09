@props(['stages', 'title'])

<x-admin.card class="mb-6">
    <h2 class="mb-3 text-sm font-semibold text-erp-primary">{{ $title ?? __('Production Pipeline') }}</h2>
    @if ($stages === [])
        <p class="text-sm text-slate-500">{{ __('Report not enabled yet.') }}</p>
    @else
        <div class="flex flex-wrap gap-2">
            @foreach ($stages as $index => $stage)
                @if ($index > 0)
                    <span class="self-center text-slate-300 px-1" aria-hidden="true">→</span>
                @endif
                <div class="min-w-[6.5rem] rounded-lg border border-erp-border bg-erp-page/60 px-3 py-2">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ $stage['label'] }}</p>
                    <p class="mt-1 text-lg font-bold tabular-nums text-erp-primary">{{ number_format($stage['count']) }}</p>
                </div>
            @endforeach
        </div>
    @endif
</x-admin.card>

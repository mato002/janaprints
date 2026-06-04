@php
    $colors = [
        'amber' => 'border-amber-300 bg-amber-50',
        'indigo' => 'border-indigo-300 bg-indigo-50',
        'slate' => 'border-slate-300 bg-slate-50',
        'emerald' => 'border-emerald-300 bg-emerald-50',
    ];
@endphp

<x-admin.card class="mt-4">
    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Production Pipeline') }}</h2>
    <div class="flex flex-col gap-2 lg:flex-row lg:items-stretch lg:justify-between">
        @foreach ($pipeline as $index => $stage)
            @php $box = $colors[$stage['color'] ?? 'slate'] ?? $colors['slate']; @endphp
            <div class="flex min-w-0 flex-1 flex-col items-center">
                @if ($stage['url'] ?? null)
                    <a href="{{ $stage['url'] }}" class="w-full rounded-lg border px-3 py-3 text-center transition-colors hover:border-erp-accent {{ $box }}" data-turbo-frame="erp-main">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">{{ $stage['label'] }}</p>
                        <p class="mt-1 text-xl font-bold tabular-nums text-erp-primary">{{ $stage['count'] }}</p>
                    </a>
                @else
                    <div class="w-full rounded-lg border px-3 py-3 text-center {{ $box }}">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">{{ $stage['label'] }}</p>
                        <p class="mt-1 text-xl font-bold tabular-nums text-erp-primary">{{ $stage['count'] }}</p>
                    </div>
                @endif
                @if ($index < count($pipeline) - 1)
                    <span class="my-1 text-slate-300" aria-hidden="true">↓</span>
                @endif
            </div>
        @endforeach
    </div>
</x-admin.card>

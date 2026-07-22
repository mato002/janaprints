@php
    $toneClasses = fn (string $tone, bool $highlight) => match (true) {
        $highlight && $tone === 'amber' => 'text-amber-600',
        $highlight && $tone === 'rose' => 'text-rose-600',
        $highlight && $tone === 'blue' => 'text-blue-600',
        default => 'text-erp-primary',
    };
@endphp

<div class="mb-4">
    <p class="mb-2 px-1 text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __("Today's store work") }}</p>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
        @foreach ($workQueue['summary'] as $card)
            <div class="rounded-lg border border-erp-border bg-white px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $card['label'] }}</p>
                <p @class(['mt-1 text-2xl font-bold tabular-nums', $toneClasses($card['tone'], $card['highlight'])])>{{ $card['value'] }}</p>
            </div>
        @endforeach
    </div>
</div>

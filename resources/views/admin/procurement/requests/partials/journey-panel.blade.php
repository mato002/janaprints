<div class="procurement-journey">
    <h3 class="text-sm font-semibold text-slate-900">{{ __('Procurement journey') }}</h3>
    <p class="mt-1 text-xs text-slate-500">{{ $journey['conversion_path'] }}</p>
    <ol class="mt-4 space-y-0">
        @foreach ($journey['steps'] as $step)
            <li class="procurement-journey__step">
                <div class="flex items-start gap-3">
                    <span @class([
                        'mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[10px] font-semibold uppercase',
                        'bg-emerald-100 text-emerald-700' => $step['state'] === 'complete',
                        'bg-sky-100 text-sky-700' => $step['state'] === 'active',
                        'bg-slate-100 text-slate-400' => in_array($step['state'], ['pending', 'skipped'], true),
                    ])>
                        @if ($step['state'] === 'complete')
                            ✓
                        @elseif ($step['state'] === 'skipped')
                            —
                        @else
                            {{ $loop->iteration }}
                        @endif
                    </span>
                    <div class="min-w-0 flex-1 pb-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-medium text-slate-900">{{ $step['label'] }}</span>
                            @if ($step['state'] === 'skipped')
                                <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide text-slate-500">{{ __('Skipped') }}</span>
                            @endif
                        </div>
                        @if ($step['document'])
                            @if ($step['route'])
                                <a href="{{ $step['route'] }}" class="mt-1 inline-block text-sm text-erp-primary hover:underline">{{ $step['document'] }}</a>
                            @else
                                <p class="mt-1 text-sm text-slate-600">{{ $step['document'] }}</p>
                            @endif
                        @else
                            <p class="mt-1 text-sm text-slate-400">{{ __('Pending') }}</p>
                        @endif
                    </div>
                </div>
                @unless ($loop->last)
                    <div class="ml-3 border-l border-dashed border-slate-200 pl-6 text-center text-slate-300" aria-hidden="true">↓</div>
                @endunless
            </li>
        @endforeach
    </ol>
</div>

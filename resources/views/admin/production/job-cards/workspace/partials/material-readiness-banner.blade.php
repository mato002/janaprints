@php
    $readiness = $materialReadiness ?? null;
@endphp

@if (is_array($readiness))
    @php
        $status = $readiness['status'] ?? 'unknown';
        $ready = (bool) ($readiness['ready'] ?? false);
        $percent = (int) ($readiness['percent'] ?? 0);
        $missing = $readiness['missing'] ?? [];
        $materialsUrl = $readiness['materials_url'] ?? route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'materials']);
        $tone = match ($status) {
            'ready' => 'border-emerald-200 bg-emerald-50 text-emerald-950',
            'blocked' => 'border-rose-200 bg-rose-50 text-rose-950',
            default => 'border-amber-200 bg-amber-50 text-amber-950',
        };
        $badgeTone = match ($status) {
            'ready' => 'bg-emerald-600 text-white',
            'blocked' => 'bg-rose-600 text-white',
            default => 'bg-amber-500 text-white',
        };
    @endphp

    <section class="mt-3 rounded-lg border px-4 py-3 {{ $tone }}" aria-label="{{ __('Material readiness') }}">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-sm font-semibold tracking-wide">{{ __('Material Readiness') }}</h2>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide {{ $badgeTone }}">
                        {{ $percent }}% · {{ $readiness['label'] ?? '' }}
                    </span>
                    @if ($ready)
                        <span class="text-xs font-medium">{{ __('Ready for release') }}</span>
                    @elseif (! ($readiness['has_requirements'] ?? false))
                        <span class="text-xs font-medium">{{ __('Requirements not generated') }}</span>
                    @else
                        <span class="text-xs font-medium">{{ __('Release blocked') }}</span>
                    @endif
                </div>
                <p class="mt-1 text-xs leading-relaxed opacity-90">{{ $readiness['detail'] ?? '' }}</p>

                @if (count($missing) > 0)
                    <ul class="mt-2 space-y-1 text-xs">
                        @foreach (array_slice($missing, 0, 5) as $line)
                            <li>
                                <span class="font-semibold">{{ $line['item'] }}</span>
                                @if (! empty($line['sku']))
                                    <span class="opacity-70">({{ $line['sku'] }})</span>
                                @endif
                                — {{ __('short by') }}
                                <span class="font-semibold tabular-nums">
                                    {{ rtrim(rtrim(number_format((float) $line['shortfall'], 3, '.', ''), '0'), '.') }}
                                    {{ $line['unit'] ?? '' }}
                                </span>
                                <span class="opacity-70">
                                    ({{ __('available') }}
                                    {{ rtrim(rtrim(number_format((float) $line['available'], 3, '.', ''), '0'), '.') }})
                                </span>
                            </li>
                        @endforeach
                        @if (count($missing) > 5)
                            <li class="opacity-80">{{ __('and :count more…', ['count' => count($missing) - 5]) }}</li>
                        @endif
                    </ul>
                @endif
            </div>

            <a href="{{ $materialsUrl }}" class="shrink-0 text-xs font-semibold underline underline-offset-2 hover:opacity-80">
                {{ $ready ? __('View materials') : __('Resolve shortages') }}
            </a>
        </div>
    </section>
@endif

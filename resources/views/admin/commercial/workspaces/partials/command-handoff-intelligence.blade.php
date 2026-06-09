@php
    $handoff = $handoffIntelligence ?? ['summary' => [], 'sections' => []];
    $summary = $handoff['summary'] ?? [];
    $sections = $handoff['sections'] ?? [];
    $totalBlocked = collect($summary)->sum();
@endphp

@if (! empty($sections))
    <section class="mb-6" aria-label="{{ __('Handoff intelligence center') }}">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Handoff Intelligence Center') }}</h2>
                <p class="mt-1 text-sm text-slate-600">{{ __('Commercial documents stuck between workflow stages.') }}</p>
            </div>
        </div>

        @if ($totalBlocked > 0)
            <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ([
                    'critical' => ['label' => __('Critical'), 'class' => 'border-red-200 bg-red-50 text-red-800'],
                    'high' => ['label' => __('High'), 'class' => 'border-amber-200 bg-amber-50 text-amber-800'],
                    'medium' => ['label' => __('Medium'), 'class' => 'border-sky-200 bg-sky-50 text-sky-800'],
                    'low' => ['label' => __('Low'), 'class' => 'border-slate-200 bg-slate-50 text-slate-700'],
                ] as $level => $meta)
                    <div class="rounded-xl border px-4 py-3 {{ $meta['class'] }}">
                        <p class="text-[11px] font-semibold uppercase tracking-wide opacity-80">{{ $meta['label'] }}</p>
                        <p class="mt-1 text-2xl font-bold tabular-nums">{{ number_format($summary[$level] ?? 0) }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            @foreach ($sections as $section)
                <x-admin.card>
                    <div class="border-b border-erp-border px-4 py-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-erp-primary">{{ $section['title'] }}</h3>
                                <p class="mt-1 text-xs text-slate-500">{{ $section['description'] }}</p>
                            </div>
                            <span class="erp-badge">{{ $section['count'] }}</span>
                        </div>
                        @if (! empty($section['route']) && Route::has($section['route']))
                            <a href="{{ route($section['route']) }}" data-turbo-frame="erp-main" class="mt-2 inline-block text-xs font-medium text-erp-accent hover:text-erp-accent-hover">
                                {{ __('Resolve') }}
                            </a>
                        @endif
                    </div>
                    <div class="overflow-x-auto">
                        <table class="erp-table w-full text-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('Reference') }}</th>
                                    <th>{{ __('Customer') }}</th>
                                    <th>{{ __('Value') }}</th>
                                    <th>{{ __('Age') }}</th>
                                    <th>{{ __('Score') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($section['items'] as $item)
                                    <tr>
                                        <td>
                                            @if (! empty($item['url']))
                                                <a href="{{ $item['url'] }}" data-turbo-frame="erp-main" class="font-medium text-erp-accent hover:text-erp-accent-hover">{{ $item['reference'] }}</a>
                                            @else
                                                {{ $item['reference'] }}
                                            @endif
                                            @if (! empty($item['context']))
                                                <span class="block text-[11px] text-slate-500">{{ $item['context'] }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $item['customer'] }}</td>
                                        <td class="font-mono">{{ $item['value'] }}</td>
                                        <td class="text-slate-600">{{ $item['age_label'] }}</td>
                                        <td>
                                            <span @class([
                                                'inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold',
                                                'bg-red-100 text-red-800' => ($item['attention_variant'] ?? '') === 'danger',
                                                'bg-amber-100 text-amber-800' => ($item['attention_variant'] ?? '') === 'warning',
                                                'bg-sky-100 text-sky-800' => ($item['attention_variant'] ?? '') === 'info',
                                                'bg-slate-100 text-slate-700' => ($item['attention_variant'] ?? '') === 'neutral',
                                            ])>
                                                {{ $item['attention_label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 text-center text-slate-500">{{ __('No blocked handoffs.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-admin.card>
            @endforeach
        </div>
    </section>
@endif

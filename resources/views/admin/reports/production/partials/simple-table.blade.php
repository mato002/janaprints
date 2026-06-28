@props(['title', 'columns', 'rows', 'highlight_utilization' => false])

@php
    $rowCount = count($rows ?? []);
@endphp

<x-admin.card :padding="false" class="h-full">
    <div class="flex items-center justify-between gap-3 border-b border-erp-border px-4 py-3">
        <h3 class="text-sm font-semibold text-erp-primary">{{ $title }}</h3>
        <span class="text-xs text-slate-500 tabular-nums">
            {{ trans_choice(':count row|:count rows', $rowCount, ['count' => $rowCount]) }}
        </span>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    @foreach ($columns as $column)
                        <th>{{ $column }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        @foreach ((array) $row as $index => $cell)
                            <td @class([
                                'tabular-nums' => $index > 0,
                                'font-medium text-slate-900' => $index === 0,
                                'whitespace-nowrap' => $index === 0,
                            ])>
                                @if ($highlight_utilization && $index === count((array) $row) - 1 && is_string($cell) && str_ends_with($cell, '%'))
                                    @php $pct = (int) preg_replace('/\D/', '', (string) $cell); @endphp
                                    <div class="flex min-w-[7rem] items-center gap-2">
                                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-200">
                                            <div class="h-full rounded-full bg-erp-accent" style="width: {{ min(100, max(0, $pct)) }}%"></div>
                                        </div>
                                        <span class="w-10 text-right text-xs font-medium">{{ $cell }}</span>
                                    </div>
                                @else
                                    {{ $cell }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) }}" class="py-8 text-center text-slate-500">
                            {{ __('No data for selected period.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin.card>

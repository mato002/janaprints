@php
    $columns = $table['columns'] ?? [];
    $rows = $table['rows'] ?? [];
    $totals = $table['totals'] ?? [];
    $title = $table['title'] ?? __('Register');
    $print = $print ?? false;
@endphp

<x-admin.card :padding="false">
    <div class="border-b border-erp-border px-4 py-3">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ $title }}</h2>
        <p class="mt-0.5 text-xs text-slate-500">{{ __('Read-only register generated from live ERP data') }}</p>
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
                    @php
                        $values = $row['values'] ?? (array) $row;
                        $links = $row['links'] ?? [];
                    @endphp
                    <tr>
                        @foreach ($values as $index => $cell)
                            <td @class(['tabular-nums' => $index > 0, 'whitespace-nowrap' => $index === 0])>
                                @if (! $print && isset($links[$index]) && filled($links[$index]))
                                    <a href="{{ $links[$index] }}" class="text-erp-primary hover:underline" data-turbo-frame="erp-main">{{ $cell }}</a>
                                @else
                                    {{ $cell }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ max(count($columns), 1) }}" class="py-8 text-center text-slate-500">
                            {{ __('No data for selected period.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if ($totals !== [])
                <tfoot>
                    <tr class="bg-slate-50 font-semibold">
                        @foreach ($totals as $cell)
                            <td class="tabular-nums">{{ $cell }}</td>
                        @endforeach
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</x-admin.card>

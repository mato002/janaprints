@props(['title', 'columns', 'rows'])

<div>
    <h3 class="mb-3 text-sm font-semibold text-erp-primary">{{ $title }}</h3>
    @if ($rows === [])
        <p class="py-6 text-center text-sm text-slate-500">{{ __('No data for selected filters.') }}</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-erp-border text-left text-[11px] uppercase tracking-wide text-slate-500">
                        @foreach ($columns as $column)
                            <th class="px-3 py-2 font-semibold">{{ $column }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr class="border-b border-erp-border/60">
                            @foreach ($row as $cell)
                                <td class="px-3 py-2 tabular-nums text-slate-700">{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

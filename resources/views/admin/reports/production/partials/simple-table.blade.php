@props(['title', 'columns', 'rows'])

<div>
    <h3 class="section-title mb-3 text-sm font-semibold text-erp-primary">{{ $title }}</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-erp-border text-left text-[11px] uppercase tracking-wide text-slate-500">
                    @foreach ($columns as $column)
                        <th class="px-3 py-2 font-semibold">{{ $column }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr class="border-b border-erp-border/60">
                        @foreach ((array) $row as $cell)
                            <td class="px-3 py-2 tabular-nums">{{ $cell }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) }}" class="px-3 py-6 text-center text-slate-500">
                            {{ __('No data for selected filters.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

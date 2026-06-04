@props(['rows', 'title'])

<x-admin.card class="mb-6">
    <h2 class="mb-3 text-sm font-semibold text-erp-primary">{{ $title ?? __('Branch Performance') }}</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-erp-border text-left text-[11px] uppercase tracking-wide text-slate-500">
                    <th class="px-3 py-2 font-semibold">{{ __('Branch') }}</th>
                    <th class="px-3 py-2 font-semibold text-right">{{ __('Sales') }}</th>
                    <th class="px-3 py-2 font-semibold text-right">{{ __('Jobs') }}</th>
                    <th class="px-3 py-2 font-semibold text-right">{{ __('Receivables') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr class="border-b border-erp-border/60">
                        <td class="px-3 py-2 font-medium text-erp-primary">{{ $row['name'] }}</td>
                        <td class="px-3 py-2 text-right tabular-nums">{{ $row['sales'] }}</td>
                        <td class="px-3 py-2 text-right tabular-nums">{{ $row['jobs'] }}</td>
                        <td class="px-3 py-2 text-right tabular-nums text-slate-500">{{ $row['receivables'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-3 py-6 text-center text-slate-500">{{ __('No branch data for selected filters.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin.card>

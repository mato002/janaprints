@props(['readiness', 'report_ready'])

<x-admin.card class="mb-6">
    <div class="mb-3 flex items-center justify-between gap-3">
        <div>
            <h2 class="text-sm font-semibold text-erp-primary">{{ __('Data Readiness') }}</h2>
            <p class="mt-1 text-xs text-slate-500">{{ __('Operational sources required before sales order reports can run.') }}</p>
        </div>
        <span @class([
            'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold',
            'bg-emerald-50 text-emerald-700' => $report_ready,
            'bg-amber-50 text-amber-700' => ! $report_ready,
        ])>
            {{ $report_ready ? __('Ready') : __('Not Ready') }}
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-erp-border text-left text-[11px] uppercase tracking-wide text-slate-500">
                    <th class="px-3 py-2 font-semibold">{{ __('Source') }}</th>
                    <th class="px-3 py-2 font-semibold">{{ __('Table') }}</th>
                    <th class="px-3 py-2 font-semibold">{{ __('Status') }}</th>
                    <th class="px-3 py-2 font-semibold">{{ __('Notes') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($readiness as $row)
                    <tr class="border-b border-erp-border/60">
                        <td class="px-3 py-2 font-medium text-erp-primary">{{ $row['source'] }}</td>
                        <td class="px-3 py-2 font-mono text-xs text-slate-600">{{ $row['table'] }}</td>
                        <td class="px-3 py-2">
                            <span @class([
                                'inline-flex rounded-full px-2 py-0.5 text-xs font-semibold',
                                'bg-emerald-50 text-emerald-700' => $row['ready'],
                                'bg-rose-50 text-rose-700' => ! $row['ready'],
                            ])>
                                {{ $row['ready'] ? __('Ready') : __('Unavailable') }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-slate-600">{{ $row['notes'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-admin.card>

@props(['rows', 'active_dimension'])

<x-admin.card class="mb-6">
    <h3 class="mb-3 text-sm font-semibold text-erp-primary">
        {{ __('Dashboard') }} — {{ ucfirst($active_dimension) }}
    </h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-erp-border text-left text-[11px] uppercase tracking-wide text-slate-500">
                    <th class="px-3 py-2">{{ __('Name') }}</th>
                    <th class="px-3 py-2">{{ __('Attendance %') }}</th>
                    <th class="px-3 py-2">{{ __('Headcount') }}</th>
                    <th class="px-3 py-2">{{ __('Payroll Cost') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr class="border-b border-erp-border/60">
                        <td class="px-3 py-2">{{ $row['name'] }}</td>
                        <td class="px-3 py-2 tabular-nums">{{ $row['attendance_percent'] }}%</td>
                        <td class="px-3 py-2 tabular-nums">{{ $row['headcount'] }}</td>
                        <td class="px-3 py-2 tabular-nums">{{ number_format($row['payroll_cost'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-3 py-6 text-center text-slate-500">{{ __('No data for selected filters.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin.card>

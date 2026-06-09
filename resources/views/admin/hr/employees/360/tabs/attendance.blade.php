<div class="grid gap-4 sm:grid-cols-4 mb-4">
    @foreach ([
        ['label' => __('Present'), 'value' => $attendance['summary']['present']],
        ['label' => __('Late Arrivals'), 'value' => $attendance['summary']['late']],
        ['label' => __('Absent'), 'value' => $attendance['summary']['absent']],
        ['label' => __('Overtime (hrs)'), 'value' => $attendance['summary']['overtime_hours']],
    ] as $card)
        <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" />
    @endforeach
</div>

<x-admin.card class="mb-4">
    <h3 class="mb-3 font-semibold text-erp-primary">{{ __('Attendance Register') }} ({{ now()->format('F Y') }})</h3>
    <x-admin.data-table>
        <x-slot name="head">
            <tr><th>{{ __('Date') }}</th><th>{{ __('Status') }}</th><th>{{ __('Late (min)') }}</th><th>{{ __('Overtime') }}</th></tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($attendance['records'] as $record)
                <tr>
                    <td>{{ $record->attendance_date?->format('M j, Y') }}</td>
                    <td>{{ ucfirst($record->status?->value ?? '') }}</td>
                    <td>{{ $record->late_minutes }}</td>
                    <td>{{ $record->overtime_hours }}</td>
                </tr>
            @empty
                <tr><td colspan="4"><x-admin.empty-state :title="__('No attendance records this month')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$attendance['records']" /></x-slot>
    </x-admin.data-table>
</x-admin.card>

<x-admin.card>
    <h3 class="mb-3 font-semibold text-erp-primary">{{ __('Leave Calendar Overlay') }}</h3>
    <p class="text-sm text-slate-600">{{ __('Days with approved leave highlighted in register context.') }}</p>
    <div class="mt-3 grid grid-cols-7 gap-1 text-center text-xs">
        @foreach ($attendance['calendar_month']->take(31) as $day)
            <div class="rounded p-1 {{ $day['requests']->isNotEmpty() ? 'bg-amber-100 text-amber-900' : 'bg-slate-50' }}">
                {{ \Illuminate\Support\Carbon::parse($day['date'])->format('j') }}
            </div>
        @endforeach
    </div>
</x-admin.card>

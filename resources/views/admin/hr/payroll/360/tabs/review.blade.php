@if (! ($review['can_submit_for_approval'] ?? true))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        {{ __('Critical review issues must be resolved before this payroll can be submitted for approval.') }}
    </div>
@elseif (($review['summary']['warning_count'] ?? 0) > 0)
    <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        {{ __('Review warnings exist. You may proceed, but verify data before approval.') }}
    </div>
@else
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        {{ __('No blocking review issues detected.') }}
    </div>
@endif

<div class="mb-4 grid gap-3 sm:grid-cols-3">
    <x-admin.kpi-widget :label="__('Critical issues')" :value="(string) ($review['summary']['critical_count'] ?? 0)" />
    <x-admin.kpi-widget :label="__('Warnings')" :value="(string) ($review['summary']['warning_count'] ?? 0)" />
    <x-admin.kpi-widget :label="__('Employees on run')" :value="(string) ($review['summary']['employees_on_run'] ?? 0)" />
</div>

@foreach ([
    'critical' => __('Critical issues'),
    'warnings' => __('Warnings'),
    'informational' => __('Excluded employees'),
] as $section => $title)
    <x-admin.card class="mb-4">
        <h3 class="mb-3 text-sm font-semibold text-erp-primary">{{ $title }}</h3>
        @if (($review[$section] ?? []) === [])
            <p class="text-sm text-slate-600">{{ __('None recorded.') }}</p>
        @else
            <x-admin.data-table :export-filename="'payroll-review-'.$section">
                <x-slot name="head">
                    <tr>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Issue') }}</th>
                    </tr>
                </x-slot>
                <x-slot name="body">
                    @foreach ($review[$section] as $issue)
                        <tr>
                            <td class="font-medium">
                                {{ $issue['employee_name'] ?? '—' }}
                                <span class="block text-xs text-slate-500">{{ $issue['employee_number'] ?? '' }}</span>
                            </td>
                            <td class="text-sm">{{ $issue['message'] ?? implode(', ', $issue['problems'] ?? []) }}</td>
                        </tr>
                    @endforeach
                </x-slot>
            </x-admin.data-table>
        @endif
    </x-admin.card>
@endforeach

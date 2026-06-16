<x-admin.data-table export-filename="payroll-employees">
    <x-slot name="head">
        <tr>
            <th>{{ __('Employee') }}</th>
            <th>{{ __('Branch') }}</th>
            <th>{{ __('Gross') }}</th>
            <th>{{ __('Net') }}</th>
            <th>{{ __('Days') }}</th>
            <th>{{ __('Status') }}</th>
        </tr>
    </x-slot>
    <x-slot name="body">
        @forelse ($employees['lines'] as $line)
            <tr>
                <td class="font-medium">
                    {{ $line['employee']?->full_name }}
                    <span class="block text-xs text-slate-500">{{ $line['employee']?->employee_number }}</span>
                </td>
                <td>{{ $line['employee']?->branch?->name ?? '—' }}</td>
                <td class="tabular-nums">{{ number_format($line['payslip']->gross_pay, 2) }}</td>
                <td class="tabular-nums font-medium">{{ number_format($line['payslip']->net_pay, 2) }}</td>
                <td class="text-sm text-slate-500">{{ $line['payslip']->days_worked }}W / {{ $line['payslip']->leave_days }}L</td>
                <td>
                    @if ($line['has_warning'])
                        <span class="erp-badge erp-badge--warning">{{ __('Warning') }}</span>
                    @else
                        <span class="erp-badge erp-badge--success">{{ __('OK') }}</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6">
                    <x-admin.empty-state
                        :title="__('No employee lines')"
                        :description="__('Generate payroll to create employee lines for :count active employees.', ['count' => $employees['scoped_count']])"
                    />
                </td>
            </tr>
        @endforelse
    </x-slot>
</x-admin.data-table>

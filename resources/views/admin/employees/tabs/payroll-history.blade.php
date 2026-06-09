<x-admin.card>
    <x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th>{{ __('Run') }}</th>
                <th>{{ __('Period') }}</th>
                <th>{{ __('Gross') }}</th>
                <th>{{ __('Net') }}</th>
                <th class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($payslips as $payslip)
                <tr>
                    <td>{{ $payslip->payrollRun?->reference ?? $payslip->reference }}</td>
                    <td>
                        {{ $payslip->payrollRun?->period_start?->format('M j') }}
                        –
                        {{ $payslip->payrollRun?->period_end?->format('M j, Y') }}
                    </td>
                    <td>{{ number_format($payslip->gross_pay, 2) }}</td>
                    <td>{{ number_format($payslip->net_pay, 2) }}</td>
                    <td class="erp-table-actions-col">
                        @can('view', $payslip->payrollRun)
                            <x-admin.table-row-action :href="route('admin.hr.payroll.payslip.download', $payslip)">{{ __('PDF') }}</x-admin.table-row-action>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-admin.empty-state :title="__('No payslips yet')" /></td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
</x-admin.card>

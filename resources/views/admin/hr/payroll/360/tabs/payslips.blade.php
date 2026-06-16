<x-admin.data-table export-filename="payslips">
    <x-slot name="head">
        <tr>
            <th>{{ __('Employee') }}</th>
            <th>{{ __('Gross') }}</th>
            <th>{{ __('Net') }}</th>
            <th>{{ __('Released') }}</th>
            <th class="erp-table-actions-col">{{ __('Actions') }}</th>
        </tr>
    </x-slot>
    <x-slot name="body">
        @forelse ($payslips as $payslip)
            <tr>
                <td class="font-medium">{{ $payslip->employee?->full_name }}</td>
                <td class="tabular-nums">{{ number_format($payslip->gross_pay, 2) }}</td>
                <td class="tabular-nums font-medium">{{ number_format($payslip->net_pay, 2) }}</td>
                <td>
                    @if ($payslip->released_at)
                        <span class="erp-badge erp-badge--success">{{ $payslip->released_at->format('M j, Y') }}</span>
                    @else
                        <span class="text-sm text-slate-500">{{ __('Pending') }}</span>
                    @endif
                </td>
                <td class="erp-table-actions-col">
                    <x-admin.table-row-actions>
                        <x-admin.table-row-action :href="route('admin.hr.payroll.payslip.show', $payslip)">{{ __('View') }}</x-admin.table-row-action>
                        <x-admin.table-row-action :href="route('admin.hr.payroll.payslip.download', $payslip)">{{ __('PDF') }}</x-admin.table-row-action>
                        @can('process', $run)
                            <form method="POST" action="{{ route('admin.hr.payroll.payslip.email', $payslip) }}" class="contents">
                                @csrf
                                <button type="submit" class="erp-table-row-action w-full text-left">{{ __('Email') }}</button>
                            </form>
                        @endcan
                    </x-admin.table-row-actions>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5">
                    <x-admin.empty-state
                        :title="__('No payslips yet')"
                        :description="__('Generate payroll to create payslips for each employee line.')"
                    />
                </td>
            </tr>
        @endforelse
    </x-slot>
</x-admin.data-table>

@if ($run->status === App\Enums\PayrollRunStatus::Posted || $run->status === App\Enums\PayrollRunStatus::Paid)
    @can('export', App\Models\Hr\PayrollRun::class)
        <x-admin.card class="mt-4">
            <h3 class="mb-3 text-sm font-semibold text-erp-primary">{{ __('Payment file exports') }}</h3>
            <p class="mb-3 text-sm text-slate-600">{{ __('Placeholder exports for bank, EFT, and M-Pesa payroll disbursement files.') }}</p>
            <div class="flex flex-wrap gap-2">
                @foreach (['bank' => __('Bank file'), 'eft' => __('EFT file'), 'mpesa' => __('M-Pesa file')] as $format => $label)
                    <a href="{{ route('admin.hr.payroll.export-payment', ['payrollRun' => $run, 'format' => $format]) }}" class="erp-btn-secondary">{{ $label }}</a>
                @endforeach
            </div>
        </x-admin.card>
    @endcan
@endif

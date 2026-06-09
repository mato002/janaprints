<x-admin-layout :title="$run->reference" :breadcrumbs="[['label' => __('Payroll'), 'url' => route('admin.hr.payroll.dashboard')], ['label' => $run->reference]]">
    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-erp-primary">{{ $run->reference }}</h2>
            <p class="text-sm text-slate-600">{{ $run->period_start?->format('M j, Y') }} – {{ $run->period_end?->format('M j, Y') }} · {{ __('Pay') }} {{ $run->pay_date?->format('M j, Y') }}</p>
        </div>
        <span class="erp-badge erp-badge--{{ $run->status?->badgeClass() }}">{{ $run->status?->label() }}</span>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['Gross', $run->gross_total],
            ['Deductions', $run->deductions_total],
            ['Net', $run->net_total],
            ['Employees', $run->employee_count],
        ] as [$label, $value])
            <x-admin.kpi-widget :label="__($label)" :value="is_numeric($value) ? number_format($value, 2) : $value" />
        @endforeach
    </div>

    <x-admin.card class="mb-6">
        <div class="flex flex-wrap gap-2">
            @can('process', $run)
                @if (in_array($run->status, [App\Enums\PayrollRunStatus::Draft, App\Enums\PayrollRunStatus::Calculated]))
                    <form method="POST" action="{{ route('admin.hr.payroll.calculate', $run) }}">
                        @csrf
                        <button type="submit" class="erp-btn-primary">{{ __('Calculate payroll') }}</button>
                    </form>
                @endif
            @endcan
            @can('approve', $run)
                @if ($run->status === App\Enums\PayrollRunStatus::Calculated)
                    <form method="POST" action="{{ route('admin.hr.payroll.approve', $run) }}">
                        @csrf
                        <button type="submit" class="erp-btn-secondary">{{ __('Approve') }}</button>
                    </form>
                @endif
                @if ($run->status === App\Enums\PayrollRunStatus::Approved)
                    <form method="POST" action="{{ route('admin.hr.payroll.post', $run) }}">
                        @csrf
                        <button type="submit" class="erp-btn-secondary">{{ __('Post to accounting') }}</button>
                    </form>
                @endif
            @endcan
            @can('export', App\Models\Hr\PayrollRun::class)
                <x-admin.export-dropdown
                    export-route="admin.hr.payroll.export"
                    :export-route-params="['payrollRun' => $run]"
                />
            @endcan
        </div>
        @if ($run->postedJournal)
            <p class="mt-3 text-sm text-slate-600">{{ __('Journal posted') }}: {{ $run->postedJournal->reference ?? $run->postedJournal->id }}</p>
        @endif
    </x-admin.card>

    <x-admin.data-table export-filename="payslips">
        <x-slot name="head">
            <tr>
                <th>{{ __('Employee') }}</th>
                <th>{{ __('Gross') }}</th>
                <th>{{ __('PAYE') }}</th>
                <th>{{ __('SHIF') }}</th>
                <th>{{ __('NSSF') }}</th>
                <th>{{ __('Housing') }}</th>
                <th>{{ __('Net') }}</th>
                <th>{{ __('Days') }}</th>
                <th class="erp-table-actions-col">{{ __('Payslip') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($run->payslips as $payslip)
                <tr>
                    <td class="font-medium">{{ $payslip->employee?->full_name }}</td>
                    <td class="tabular-nums">{{ number_format($payslip->gross_pay, 2) }}</td>
                    <td class="tabular-nums">{{ number_format($payslip->paye, 2) }}</td>
                    <td class="tabular-nums">{{ number_format($payslip->shif, 2) }}</td>
                    <td class="tabular-nums">{{ number_format($payslip->nssf, 2) }}</td>
                    <td class="tabular-nums">{{ number_format($payslip->housing_levy, 2) }}</td>
                    <td class="tabular-nums font-medium">{{ number_format($payslip->net_pay, 2) }}</td>
                    <td class="text-sm text-slate-500">{{ $payslip->days_worked }}W / {{ $payslip->leave_days }}L</td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.hr.payroll.payslip.download', $payslip)">{{ __('PDF') }}</x-admin.table-row-action>
                            <form method="POST" action="{{ route('admin.hr.payroll.payslip.email', $payslip) }}" class="contents">
                                @csrf
                                <button type="submit" class="erp-table-row-action w-full text-left">{{ __('Email') }}</button>
                            </form>
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9"><x-admin.empty-state :title="__('No payslips yet')" :description="__('Calculate payroll to generate payslips.')" /></td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
</x-admin-layout>

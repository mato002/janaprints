<x-admin-layout
    :title="__('Payslip').' · '.($payslip->employee?->full_name ?? $payslip->reference)"
    :breadcrumbs="[['label' => __('Payroll'), 'url' => route('admin.hr.payroll.dashboard')], ['label' => $payslip->payrollRun?->reference, 'url' => route('admin.hr.payroll.show', $payslip->payrollRun)], ['label' => __('Payslip')]]"
>
    <x-admin.card>
        <div class="mb-4 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-erp-primary">{{ $payslip->employee?->full_name }}</h2>
                <p class="text-sm text-slate-600">
                    {{ $payslip->payrollRun?->period_start?->format('M j, Y') }} – {{ $payslip->payrollRun?->period_end?->format('M j, Y') }}
                </p>
            </div>
            <div class="flex gap-2">
                <x-documents.pdf-download-button
                    :url="route('admin.hr.payroll.payslip.download', $payslip)"
                    :filename="($payslip->reference ?? 'payslip-'.$payslip->id).'.pdf'"
                />
                @can('process', $payslip->payrollRun)
                    @if ($payslip->employee?->email)
                        <form method="POST" action="{{ route('admin.hr.payroll.payslip.email', $payslip) }}" class="inline" data-turbo="false">
                            @csrf
                            <button type="submit" class="erp-btn-secondary">
                                {{ $payslip->emailed_at ? __('Resend email') : __('Email payslip') }}
                            </button>
                        </form>
                    @endif
                @endcan
                @if ($payslip->released_at)
                    <span class="erp-badge erp-badge--success">{{ __('Released') }}</span>
                @endif
                @if ($payslip->emailed_at)
                    <span class="erp-badge erp-badge--success">{{ __('Emailed :date', ['date' => $payslip->emailed_at->format('M j, Y')]) }}</span>
                @endif
            </div>
        </div>

        <dl class="mb-6 grid gap-4 sm:grid-cols-3">
            <div><dt class="text-xs text-slate-500">{{ __('Basic salary') }}</dt><dd class="font-medium tabular-nums">{{ number_format($payslip->basic_salary, 2) }}</dd></div>
            <div><dt class="text-xs text-slate-500">{{ __('Gross pay') }}</dt><dd class="font-medium tabular-nums">{{ number_format($payslip->gross_pay, 2) }}</dd></div>
            <div><dt class="text-xs text-slate-500">{{ __('Net pay') }}</dt><dd class="font-medium tabular-nums">{{ number_format($payslip->net_pay, 2) }}</dd></div>
        </dl>

        <div class="grid gap-6 lg:grid-cols-2">
            <div>
                <h3 class="mb-2 text-sm font-semibold">{{ __('Earnings') }}</h3>
                <div class="overflow-x-auto rounded-lg border border-erp-border">
                    <table class="erp-table w-full">
                        <thead>
                            <tr>
                                <th>{{ __('Item') }}</th>
                                <th class="text-right">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payslip->items->where('item_type', App\Enums\PayrollItemType::Allowance) as $item)
                                <tr>
                                    <td>{{ $item->name }}</td>
                                    <td class="tabular-nums text-right">{{ number_format($item->amount, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr class="bg-slate-50">
                                <th scope="row">{{ __('Gross pay') }}</th>
                                <th class="tabular-nums text-right">{{ number_format($payslip->gross_pay, 2) }}</th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div>
                <h3 class="mb-2 text-sm font-semibold">{{ __('Deductions & statutories') }}</h3>
                <div class="overflow-x-auto rounded-lg border border-erp-border">
                    <table class="erp-table w-full">
                        <thead>
                            <tr>
                                <th>{{ __('Item') }}</th>
                                <th class="text-right">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payslip->items->where('item_type', App\Enums\PayrollItemType::Deduction) as $item)
                                <tr>
                                    <td>{{ $item->name }}</td>
                                    <td class="tabular-nums text-right">{{ number_format($item->amount, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr class="bg-slate-50">
                                <th scope="row">{{ __('Total deductions') }}</th>
                                <th class="tabular-nums text-right">{{ number_format($payslip->total_deductions, 2) }}</th>
                            </tr>
                            <tr class="bg-emerald-50">
                                <th scope="row">{{ __('Net pay') }}</th>
                                <th class="tabular-nums text-right text-emerald-800">{{ number_format($payslip->net_pay, 2) }}</th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </x-admin.card>
</x-admin-layout>

<p class="mb-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
    {{ __('Payslips are emailed automatically when you release them after posting. Use Email or Resend email for individual staff when needed.') }}
</p>

<x-admin.data-table export-filename="payslips">
    <x-slot name="head">
        <tr>
            <th>{{ __('Employee') }}</th>
            <th>{{ __('Gross') }}</th>
            <th>{{ __('Net') }}</th>
            <th>{{ __('Released') }}</th>
            <th>{{ __('Emailed') }}</th>
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
                <td>
                    @if ($payslip->emailed_at)
                        <span class="erp-badge erp-badge--success">{{ $payslip->emailed_at->format('M j, Y') }}</span>
                    @else
                        <span class="text-sm text-slate-500">{{ __('Not sent') }}</span>
                    @endif
                </td>
                <td class="erp-table-actions-col">
                    <x-admin.table-row-actions>
                        <x-admin.table-row-action :href="route('admin.hr.payroll.payslip.show', $payslip)">{{ __('View') }}</x-admin.table-row-action>
                        <button
                            type="button"
                            class="flex w-full items-center gap-2 px-3 py-2 text-sm text-erp-primary hover:bg-erp-page"
                            data-document-pdf-download
                            data-document-pdf-download-url="{{ route('admin.hr.payroll.payslip.download', $payslip) }}"
                            data-document-pdf-download-filename="{{ ($payslip->reference ?? 'payslip-'.$payslip->id).'.pdf' }}"
                            @click="$dispatch('erp-row-menu-close')"
                        >
                            {{ __('PDF') }}
                        </button>
                        @can('process', $run)
                            @if ($payslip->employee?->email)
                                <form method="POST" action="{{ route('admin.hr.payroll.payslip.email', $payslip) }}" class="contents" data-turbo="false">
                                    @csrf
                                    <button type="submit" class="erp-table-row-action w-full text-left">
                                        {{ $payslip->emailed_at ? __('Resend email') : __('Email') }}
                                    </button>
                                </form>
                            @endif
                        @endcan
                    </x-admin.table-row-actions>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6">
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

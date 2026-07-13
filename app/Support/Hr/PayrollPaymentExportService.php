<?php

namespace App\Support\Hr;

use App\Models\Hr\PayrollRun;
use App\Models\User;
use App\Support\Export\TabularExportWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollPaymentExportService
{
    public function __construct(
        protected TabularExportWriter $writer,
        protected PayrollAuditService $audit,
    ) {}

    public function export(PayrollRun $run, string $format, User $user): StreamedResponse
    {
        $run->load(['payslips.employee']);

        $response = match ($format) {
            'bank' => $this->bankFile($run),
            'eft' => $this->eftFile($run),
            'mpesa' => $this->mpesaFile($run),
            default => throw \Illuminate\Validation\ValidationException::withMessages([
                'format' => __('Unsupported payment export format.'),
            ]),
        };

        $this->audit->logPaymentExport($run, $user, $format);

        return $response;
    }

    protected function bankFile(PayrollRun $run): StreamedResponse
    {
        $headers = [
            'Employee Number',
            'Employee Name',
            'Bank Name',
            'Account Number',
            'Branch Code',
            'Net Pay',
            'Reference',
        ];

        $rows = $run->payslips->map(fn ($slip) => [
            $slip->employee?->employee_number,
            $slip->employee?->full_name,
            $slip->employee?->bank_name ?? '',
            $slip->employee?->bank_account_number ?? '',
            $slip->employee?->bank_branch_code ?? '',
            $slip->net_pay,
            $slip->reference,
        ])->all();

        return $this->writer->download(
            'csv',
            $run->reference.'-bank-payment',
            $headers,
            $rows,
            __('Bank Payment File — :reference', ['reference' => $run->reference]),
            __('Standard bank payment CSV — columns: employee, bank, account, branch code, net pay.'),
        );
    }

    protected function eftFile(PayrollRun $run): StreamedResponse
    {
        $headers = ['Beneficiary Name', 'Account', 'Amount', 'Narration'];
        $rows = $run->payslips->map(fn ($slip) => [
            $slip->employee?->full_name,
            $slip->employee?->bank_account_number ?? '',
            $slip->net_pay,
            $run->reference,
        ])->all();

        return $this->writer->download(
            'csv',
            $run->reference.'-eft',
            $headers,
            $rows,
            __('EFT Payment File — :reference', ['reference' => $run->reference]),
            __('Standard EFT CSV — map columns to your bank upload template as needed.'),
        );
    }

    protected function mpesaFile(PayrollRun $run): StreamedResponse
    {
        $headers = ['Employee Name', 'Phone', 'Amount', 'Reference'];
        $rows = $run->payslips->map(fn ($slip) => [
            $slip->employee?->full_name,
            $slip->employee?->phone ?? '',
            $slip->net_pay,
            $slip->reference,
        ])->all();

        return $this->writer->download(
            'csv',
            $run->reference.'-mpesa',
            $headers,
            $rows,
            __('M-Pesa Payroll File — :reference', ['reference' => $run->reference]),
            __('Standard M-Pesa CSV — phone and net pay ready for disbursement upload.'),
        );
    }
}

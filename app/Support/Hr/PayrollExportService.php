<?php

namespace App\Support\Hr;

use App\Models\Hr\PayrollRun;
use App\Support\Export\TabularExportWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollExportService
{
    public function __construct(
        protected TabularExportWriter $writer,
    ) {}

    public function exportRun(PayrollRun $run, string $format = 'csv'): StreamedResponse
    {
        $run->load(['payslips.employee']);

        $headers = [
            'Employee',
            'Employee Number',
            'Gross',
            'PAYE',
            'SHIF',
            'NSSF',
            'Housing Levy',
            'Deductions',
            'Net',
        ];

        $rows = $run->payslips->map(fn ($slip) => [
            $slip->employee?->full_name,
            $slip->employee?->employee_number,
            $slip->gross_pay,
            $slip->paye,
            $slip->shif,
            $slip->nssf,
            $slip->housing_levy,
            $slip->total_deductions,
            $slip->net_pay,
        ])->all();

        return $this->writer->download(
            $format,
            $run->reference.'-payslips',
            $headers,
            $rows,
            __('Payroll Run :reference', ['reference' => $run->reference]),
            trim(($run->period_start?->format('Y-m-d') ?? '…').' — '.($run->period_end?->format('Y-m-d') ?? '…')),
        );
    }
}

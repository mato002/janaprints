<?php

namespace App\Support\Hr;

use App\Models\Hr\PayrollRun;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollExportService
{
    public function exportRun(PayrollRun $run, string $format = 'csv'): StreamedResponse
    {
        $run->load(['payslips.employee']);

        $filename = $run->reference.'-payslips';

        if ($format === 'excel') {
            return response()->streamDownload(function () use ($run) {
                echo "\xEF\xBB\xBF<table border='1'><tr>";
                foreach (['Employee', 'Gross', 'PAYE', 'SHIF', 'NSSF', 'Housing Levy', 'Deductions', 'Net'] as $h) {
                    echo '<th>'.e($h).'</th>';
                }
                echo '</tr>';
                foreach ($run->payslips as $slip) {
                    echo '<tr>';
                    foreach ([
                        $slip->employee?->full_name,
                        $slip->gross_pay,
                        $slip->paye,
                        $slip->shif,
                        $slip->nssf,
                        $slip->housing_levy,
                        $slip->total_deductions,
                        $slip->net_pay,
                    ] as $cell) {
                        echo '<td>'.e((string) $cell).'</td>';
                    }
                    echo '</tr>';
                }
                echo '</table>';
            }, "{$filename}.xls", ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8']);
        }

        return response()->streamDownload(function () use ($run) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Employee', 'Employee Number', 'Gross', 'PAYE', 'SHIF', 'NSSF', 'Housing Levy', 'Deductions', 'Net']);
            foreach ($run->payslips as $slip) {
                fputcsv($handle, [
                    $slip->employee?->full_name,
                    $slip->employee?->employee_number,
                    $slip->gross_pay,
                    $slip->paye,
                    $slip->shif,
                    $slip->nssf,
                    $slip->housing_levy,
                    $slip->total_deductions,
                    $slip->net_pay,
                ]);
            }
            fclose($handle);
        }, "{$filename}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}

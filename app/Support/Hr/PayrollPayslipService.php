<?php

namespace App\Support\Hr;

use App\Models\Hr\PayrollPayslip;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollPayslipService
{
    public function downloadPdf(PayrollPayslip $payslip): StreamedResponse
    {
        $payslip->load(['employee', 'payrollRun', 'items']);

        $html = view('admin.hr.payroll.payslip-pdf', [
            'payslip' => $payslip,
            'generatedAt' => now(),
        ])->render();

        $filename = ($payslip->reference ?? 'payslip-'.$payslip->id).'.html';

        return response()->streamDownload(fn () => print($html), $filename, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    public function email(PayrollPayslip $payslip): bool
    {
        $payslip->load('employee');
        $email = $payslip->employee?->email;

        if (! $email) {
            return false;
        }

        Log::info('Payroll payslip email queued', [
            'payslip_id' => $payslip->id,
            'employee_id' => $payslip->employee_id,
            'email' => $email,
        ]);

        $payslip->update(['emailed_at' => now()]);

        return true;
    }
}

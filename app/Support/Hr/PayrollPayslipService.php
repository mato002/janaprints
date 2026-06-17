<?php

namespace App\Support\Hr;

use App\Models\Hr\PayrollPayslip;
use App\Models\User;
use App\Support\Export\PdfExportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollPayslipService
{
    public function __construct(
        protected PdfExportService $pdfExports,
        protected EmployeeEmailService $employeeEmail,
    ) {}

    public function downloadPdf(PayrollPayslip $payslip): StreamedResponse
    {
        $payslip->load(['employee.department', 'payrollRun', 'items']);

        $basename = $payslip->reference ?? 'payslip-'.$payslip->id;

        return $this->pdfExports->downloadHtml(
            $basename,
            view('admin.hr.payroll.payslip-pdf', $this->pdfExports->payslipViewData($payslip))->render(),
        );
    }

    public function email(PayrollPayslip $payslip, ?User $actor = null): bool
    {
        return $this->employeeEmail->sendPayslip($payslip, $actor);
    }
}

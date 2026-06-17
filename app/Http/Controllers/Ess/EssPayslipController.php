<?php

namespace App\Http\Controllers\Ess;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Ess\Concerns\ResolvesEmployee;
use App\Models\Hr\PayrollPayslip;
use App\Support\Ess\EssAuditService;
use App\Support\Hr\PayrollPayslipService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EssPayslipController extends Controller
{
    use ResolvesEmployee;

    public function download(PayrollPayslip $payslip, PayrollPayslipService $payslips, EssAuditService $audit): StreamedResponse
    {
        $employee = $this->essEmployee();
        $user = $this->essUser();

        abort_unless($user->can('ess.payslips.view'), 403);
        $this->assertOwnEmployee($payslip, $employee);
        abort_unless((int) $payslip->company_id === (int) $employee->company_id, 403);
        abort_unless($payslip->released_at !== null, 403);

        $audit->logPayslipDownloaded($employee, $user, $payslip->id, $payslip->reference ?? (string) $payslip->id);

        return $payslips->downloadPdf($payslip);
    }
}

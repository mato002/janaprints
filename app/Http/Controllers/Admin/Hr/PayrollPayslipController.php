<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\PayrollPayslip;
use App\Support\Hr\PayrollPayslipService;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollPayslipController extends Controller
{
    public function __construct(
        protected PayrollPayslipService $payslips,
    ) {}

    public function download(PayrollPayslip $payslip): StreamedResponse
    {
        $this->authorize('view', $payslip->payrollRun);

        return $this->payslips->downloadPdf($payslip);
    }

    public function email(PayrollPayslip $payslip): RedirectResponse
    {
        $this->authorize('view', $payslip->payrollRun);

        if (! $this->payslips->email($payslip)) {
            return back()->with('status', __('Employee has no email address on file.'));
        }

        return back()->with('status', __('Payslip email queued.'));
    }
}

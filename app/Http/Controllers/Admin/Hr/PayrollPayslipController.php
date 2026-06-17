<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\PayrollPayslip;
use App\Support\Hr\PayrollPayslipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollPayslipController extends Controller
{
    public function __construct(
        protected PayrollPayslipService $payslips,
    ) {}

    public function show(PayrollPayslip $payslip): View
    {
        $this->authorize('view', $payslip->payrollRun);

        $payslip->load(['employee', 'payrollRun', 'items']);

        return view('admin.hr.payroll.payslip-show', [
            'payslip' => $payslip,
        ]);
    }

    public function download(PayrollPayslip $payslip): StreamedResponse
    {
        $this->authorize('view', $payslip->payrollRun);

        return $this->payslips->downloadPdf($payslip);
    }

    public function email(Request $request, PayrollPayslip $payslip): RedirectResponse
    {
        $this->authorize('process', $payslip->payrollRun);

        if (! $this->payslips->email($payslip, $request->user())) {
            return back()->with('status', __('Employee has no email address on file.'));
        }

        return back()->with('status', __('Payslip email queued.'));
    }
}

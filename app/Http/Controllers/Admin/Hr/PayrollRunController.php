<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Enums\PayrollRunStatus;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Hr\PayrollRun;
use App\Support\Hr\PayrollExportService;
use App\Support\Hr\PayrollRunService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollRunController extends Controller
{
    public function __construct(
        protected PayrollRunService $payroll,
        protected PayrollExportService $exports,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', PayrollRun::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.payroll.index', [
            'runs' => $this->payroll->paginate($companyId, $request->only('status')),
            'filters' => $request->only('status'),
            'statuses' => PayrollRunStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', PayrollRun::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.payroll.create', [
            'branches' => Branch::query()->where('company_id', $companyId)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PayrollRun::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $data = $request->validate([
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where(fn ($q) => $q->where('company_id', $companyId))],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'pay_date' => ['required', 'date', 'after_or_equal:period_end'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $run = $this->payroll->create($companyId, $data, $request->user());

        return redirect()
            ->route('admin.hr.payroll.show', $run)
            ->with('status', __('Payroll run created.'));
    }

    public function show(PayrollRun $payrollRun): View
    {
        $this->authorize('view', $payrollRun);

        $payrollRun->load(['payslips.employee', 'payslips.items', 'branch', 'processedBy', 'approvedBy', 'postedJournal']);

        return view('admin.hr.payroll.show', [
            'run' => $payrollRun,
        ]);
    }

    public function calculate(Request $request, PayrollRun $payrollRun): RedirectResponse
    {
        $this->authorize('process', $payrollRun);

        $this->payroll->calculate($payrollRun, $request->user());

        return back()->with('status', __('Payroll calculated from attendance and leave data.'));
    }

    public function approve(Request $request, PayrollRun $payrollRun): RedirectResponse
    {
        $this->authorize('approve', $payrollRun);

        $this->payroll->approve($payrollRun, $request->user());

        return back()->with('status', __('Payroll run approved.'));
    }

    public function post(Request $request, PayrollRun $payrollRun): RedirectResponse
    {
        $this->authorize('approve', $payrollRun);

        $this->payroll->post($payrollRun, $request->user());

        return back()->with('status', __('Payroll posted to accounting.'));
    }

    public function export(Request $request, PayrollRun $payrollRun): StreamedResponse
    {
        $this->authorize('export', PayrollRun::class);

        return $this->exports->exportRun($payrollRun, $request->input('format', 'csv'));
    }
}

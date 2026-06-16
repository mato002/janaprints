<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Enums\PayrollRunStatus;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Hr\PayrollRun;
use App\Support\Hr\PayrollExportService;
use App\Support\Hr\PayrollPaymentExportService;
use App\Support\Hr\PayrollRun360WorkspaceService;
use App\Support\Hr\PayrollRunService;
use App\Support\Platform\FormSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollRunController extends Controller
{
    public function __construct(
        protected PayrollRunService $payroll,
        protected PayrollRun360WorkspaceService $workspace,
        protected PayrollExportService $exports,
        protected PayrollPaymentExportService $paymentExports,
        protected FormSettingsService $formSettings,
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

        return view('admin.hr.payroll.create', $this->formMeta($request));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PayrollRun::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $branchId = tenant()->branchId() ?? $request->user()->default_branch_id;

        $data = $this->formSettings->validateRequest($request, 'payroll_run.create', [
            'branch_id' => [Rule::exists('branches', 'id')->where(fn ($q) => $q->where('company_id', $companyId))],
            'period_start' => ['date'],
            'period_end' => ['date', 'after_or_equal:period_start'],
            'pay_date' => ['date', 'after_or_equal:period_end'],
            'notes' => ['string', 'max:2000'],
        ], $companyId, $branchId);

        $data = $this->formSettings->applyDefaults('payroll_run.create', $data, $companyId, $branchId);

        $run = $this->payroll->create($companyId, $data, $request->user());

        return redirect()
            ->route('admin.hr.payroll.show', $run)
            ->with('status', __('Payroll run created. Generate payroll lines from the workspace.'));
    }

    public function show(PayrollRun $payrollRun): View
    {
        $this->authorize('view', $payrollRun);

        return view('admin.hr.payroll.show', $this->workspace->build($payrollRun));
    }

    public function generate(Request $request, PayrollRun $payrollRun): RedirectResponse
    {
        $this->authorize('process', $payrollRun);

        $this->payroll->generate(
            $payrollRun,
            $request->user(),
            $request->boolean('confirm_regenerate'),
        );

        return back()->with('status', __('Payroll generated.'));
    }

    public function calculate(Request $request, PayrollRun $payrollRun): RedirectResponse
    {
        return $this->generate($request, $payrollRun);
    }

    public function submitReview(Request $request, PayrollRun $payrollRun): RedirectResponse
    {
        $this->authorize('process', $payrollRun);

        $this->payroll->submitForReview($payrollRun, $request->user());

        return back()->with('status', __('Payroll submitted for review.'));
    }

    public function submitApproval(Request $request, PayrollRun $payrollRun): RedirectResponse
    {
        $this->authorize('process', $payrollRun);

        $this->payroll->submitForApproval($payrollRun, $request->user());

        return back()->with('status', __('Payroll submitted for approval.'));
    }

    public function approve(Request $request, PayrollRun $payrollRun): RedirectResponse
    {
        $this->authorize('approve', $payrollRun);

        $this->payroll->approve($payrollRun, $request->user());

        return back()->with('status', __('Payroll run approved.'));
    }

    public function reject(Request $request, PayrollRun $payrollRun): RedirectResponse
    {
        $this->authorize('approve', $payrollRun);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->payroll->reject($payrollRun, $request->user(), $data['reason'] ?? null);

        return back()->with('status', __('Payroll run rejected and returned to review.'));
    }

    public function post(Request $request, PayrollRun $payrollRun): RedirectResponse
    {
        $this->authorize('approve', $payrollRun);

        $this->payroll->post($payrollRun, $request->user());

        return back()->with('status', __('Payroll posted to accounting.'));
    }

    public function releasePayslips(Request $request, PayrollRun $payrollRun): RedirectResponse
    {
        $this->authorize('approve', $payrollRun);

        $this->payroll->releasePayslips($payrollRun, $request->user());

        return back()->with('status', __('Payslips marked as released.'));
    }

    public function markPaid(Request $request, PayrollRun $payrollRun): RedirectResponse
    {
        $this->authorize('approve', $payrollRun);

        $this->payroll->markPaid($payrollRun, $request->user());

        return back()->with('status', __('Payroll marked as paid.'));
    }

    public function cancel(Request $request, PayrollRun $payrollRun): RedirectResponse
    {
        $this->authorize('process', $payrollRun);

        $this->payroll->cancel($payrollRun, $request->user());

        return back()->with('status', __('Payroll run cancelled.'));
    }

    public function export(Request $request, PayrollRun $payrollRun): StreamedResponse
    {
        $this->authorize('export', PayrollRun::class);

        return $this->exports->exportRun($payrollRun, $request->input('format', 'csv'));
    }

    public function exportPayment(Request $request, PayrollRun $payrollRun): StreamedResponse
    {
        $this->authorize('export', PayrollRun::class);

        $format = $request->validate([
            'format' => ['required', Rule::in(['bank', 'eft', 'mpesa'])],
        ])['format'];

        return $this->paymentExports->export($payrollRun, $format, $request->user());
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(Request $request): array
    {
        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $branchId = tenant()->branchId() ?? $request->user()->default_branch_id;

        return [
            'formFields' => $this->formSettings->resolvedFields('payroll_run.create', $companyId, $branchId),
            'branches' => Branch::query()->where('company_id', $companyId)->orderBy('name')->get(),
        ];
    }
}

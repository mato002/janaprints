<?php

namespace App\Support\Ess;

use App\Enums\EmailIdentity\EmployeeActivationStatus;
use App\Enums\EmployeeDocumentCategory;
use App\Enums\PayrollRunStatus;
use App\Models\Communications\CommunicationLog;
use App\Models\Communications\CommunicationRecipient;
use App\Models\Employee;
use App\Models\EmailIdentity\EmployeeActivation;
use App\Models\Hr\EmployeeDocument;
use App\Models\Hr\PayrollPayslip;
use App\Models\User;
use App\Models\UserSessionRecord;
use App\Support\Hr\PayrollConfidentialityService;
use Illuminate\Support\Collection;

class EssWorkspaceService
{
    public function __construct(
        protected PayrollConfidentialityService $payrollConfidentiality,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Employee $employee, User $user): array
    {
        $employee->loadMissing(['branch', 'department', 'jobTitle', 'user']);

        return [
            'employee' => $employee,
            'user' => $user,
            'overview' => $this->overview($employee, $user),
            'dashboard' => $this->dashboardWidgets($employee, $user),
            'profile' => $this->profileForm($employee),
            'payslips' => $this->payslips($employee),
            'payrollHistory' => $this->payrollHistory($employee),
            'documents' => $this->documents($employee),
            'security' => $this->security($user),
            'communications' => $this->communications($employee, $user),
            'onboarding' => $this->onboarding($employee),
            'tabs' => $this->tabs($employee),
        ];
    }

    /**
     * @return list<array{id: string, label: string}>
     */
    public function tabs(Employee $employee): array
    {
        $tabs = [
            ['id' => 'overview', 'label' => __('Overview')],
            ['id' => 'profile', 'label' => __('Profile')],
            ['id' => 'payslips', 'label' => __('Payslips')],
            ['id' => 'payroll-history', 'label' => __('Payroll History')],
            ['id' => 'documents', 'label' => __('Documents')],
            ['id' => 'security', 'label' => __('Account Security')],
            ['id' => 'communications', 'label' => __('Communications')],
        ];

        if ($this->showOnboarding($employee)) {
            $tabs[] = ['id' => 'onboarding', 'label' => __('Onboarding')];
        }

        return $tabs;
    }

    /**
     * @return array<string, mixed>
     */
    protected function overview(Employee $employee, User $user): array
    {
        $activation = EmployeeActivation::query()
            ->where('employee_id', $employee->id)
            ->latest('id')
            ->first();

        return [
            'employee_number' => $employee->employee_number,
            'name' => $employee->full_name,
            'department' => $employee->department?->name,
            'job_title' => $employee->jobTitle?->title ?? $employee->designation,
            'branch' => $employee->branch?->name,
            'supervisor' => $this->resolveSupervisor($employee)?->full_name,
            'employment_status' => $employee->employment_status?->value,
            'hire_date' => $employee->hire_date,
            'corporate_email' => $user->email,
            'personal_email' => $employee->email,
            'phone' => $employee->phone,
            'photo_url' => $employee->photo ? asset('storage/'.$employee->photo) : null,
            'activation_status' => $employee->activation_status?->value,
            'mailbox_status' => $activation?->activated_at ? __('Provisioned') : ($activation ? __('Pending') : __('Not configured')),
            'show_onboarding' => $this->showOnboarding($employee),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function dashboardWidgets(Employee $employee, User $user): array
    {
        $latestPayslip = $this->releasedPayslipsQuery($employee)->first();

        return [
            'latest_payslip' => $latestPayslip,
            'employment' => [
                'department' => $employee->department?->name,
                'job_title' => $employee->jobTitle?->title ?? $employee->designation,
                'status' => $employee->employment_status
                    ? ucfirst(str_replace('_', ' ', $employee->employment_status->value))
                    : null,
            ],
            'recent_documents' => $this->visibleDocumentsQuery($employee)->limit(3)->get(),
            'unread_communications' => 0,
            'account_status' => $user->is_active ? __('Active') : __('Inactive'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function profileForm(Employee $employee): array
    {
        return [
            'phone' => $employee->phone,
            'email' => $employee->email,
            'address' => $employee->address,
            'emergency_contact_name' => $employee->emergency_contact_name,
            'emergency_contact_phone' => $employee->emergency_contact_phone,
            'next_of_kin_name' => $employee->next_of_kin_name,
            'next_of_kin_phone' => $employee->next_of_kin_phone,
            'next_of_kin_relationship' => $employee->next_of_kin_relationship,
            'photo' => $employee->photo,
        ];
    }

    /**
     * @return Collection<int, PayrollPayslip>
     */
    protected function payslips(Employee $employee): Collection
    {
        return $this->releasedPayslipsQuery($employee)->get();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function payrollHistory(Employee $employee): array
    {
        return $this->releasedPayslipsQuery($employee)
            ->with('payrollRun')
            ->get()
            ->map(fn (PayrollPayslip $payslip) => [
                'payslip' => $payslip,
                'period_start' => $payslip->payrollRun?->period_start,
                'period_end' => $payslip->payrollRun?->period_end,
                'gross_pay' => (float) $payslip->gross_pay,
                'total_deductions' => (float) $payslip->total_deductions,
                'net_pay' => (float) $payslip->net_pay,
                'payment_status' => $this->paymentStatusLabel($payslip),
                'pay_date' => $payslip->payrollRun?->pay_date,
            ])
            ->all();
    }

    /**
     * @return Collection<int, EmployeeDocument>
     */
    protected function documents(Employee $employee): Collection
    {
        return $this->visibleDocumentsQuery($employee)->get();
    }

    /**
     * @return array<string, mixed>
     */
    protected function security(User $user): array
    {
        $sessions = UserSessionRecord::query()
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity_at')
            ->limit(10)
            ->get();

        return [
            'corporate_email' => $user->email,
            'account_status' => $user->is_active ? __('Active') : __('Inactive'),
            'last_login' => $sessions->first()?->last_activity_at,
            'password_changed_at' => null,
            'sessions' => $sessions,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function communications(Employee $employee, User $user): array
    {
        $recipientLogIds = CommunicationRecipient::query()
            ->where(function ($query) use ($employee, $user) {
                $query->where(function ($inner) use ($employee) {
                    $inner->where('recipient_type', Employee::class)
                        ->where('recipient_id', $employee->id);
                })->orWhere('email', $user->email)
                    ->orWhere('email', $employee->email);
            })
            ->pluck('communication_log_id');

        return CommunicationLog::query()
            ->where('company_id', $employee->company_id)
            ->whereIn('id', $recipientLogIds)
            ->latest('created_at')
            ->limit(50)
            ->get()
            ->map(fn (CommunicationLog $log) => [
                'id' => $log->id,
                'subject' => $log->subject,
                'channel' => $log->channel->label(),
                'status' => $log->status->label(),
                'sent_at' => $log->sent_at ?? $log->created_at,
                'preview' => $this->payrollConfidentiality->communicationLogBodyForViewer($log, $user),
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function onboarding(Employee $employee): array
    {
        $activation = EmployeeActivation::query()
            ->where('employee_id', $employee->id)
            ->latest('id')
            ->first();

        $steps = [
            ['key' => 'account_created', 'label' => __('Account Created'), 'done' => true],
            ['key' => 'activation_sent', 'label' => __('Activation Sent'), 'done' => $activation !== null],
            ['key' => 'mailbox_provisioned', 'label' => __('Mailbox Provisioned'), 'done' => $employee->user !== null],
            ['key' => 'password_set', 'label' => __('Password Set'), 'done' => $employee->activation_status === EmployeeActivationStatus::Activated],
            ['key' => 'account_activated', 'label' => __('Account Activated'), 'done' => $employee->activation_status === EmployeeActivationStatus::Activated],
        ];

        return [
            'steps' => $steps,
            'completed' => collect($steps)->every(fn ($step) => $step['done']),
        ];
    }

    protected function showOnboarding(Employee $employee): bool
    {
        return $employee->activation_status !== EmployeeActivationStatus::Activated;
    }

    protected function releasedPayslipsQuery(Employee $employee)
    {
        return PayrollPayslip::query()
            ->where('employee_id', $employee->id)
            ->whereNotNull('released_at')
            ->with(['payrollRun'])
            ->join('payroll_runs', 'payroll_runs.id', '=', 'payroll_payslips.payroll_run_id')
            ->orderByDesc('payroll_runs.pay_date')
            ->select('payroll_payslips.*');
    }

    protected function visibleDocumentsQuery(Employee $employee)
    {
        $hidden = [
            EmployeeDocumentCategory::WarningLetter->value,
            EmployeeDocumentCategory::PerformanceReview->value,
            EmployeeDocumentCategory::ExitDocument->value,
        ];

        return EmployeeDocument::query()
            ->where('employee_id', $employee->id)
            ->whereNotIn('category', $hidden)
            ->orderByDesc('created_at');
    }

    protected function paymentStatusLabel(PayrollPayslip $payslip): string
    {
        $status = $payslip->payrollRun?->status;

        return match ($status) {
            PayrollRunStatus::Paid => __('Paid'),
            PayrollRunStatus::Posted => __('Posted'),
            default => __('Processed'),
        };
    }

    protected function resolveSupervisor(Employee $employee): ?Employee
    {
        $reportsToTitleId = $employee->jobTitle?->reports_to_job_title_id;

        if (! $reportsToTitleId) {
            return null;
        }

        return Employee::query()
            ->where('company_id', $employee->company_id)
            ->where('job_title_id', $reportsToTitleId)
            ->where('is_active', true)
            ->when($employee->branch_id, fn ($q) => $q->where('branch_id', $employee->branch_id))
            ->first();
    }
}

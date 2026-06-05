<?php

namespace App\Support\Hr;

use App\Models\Employee;

class HrDashboardService
{
    public function __construct(
        protected AttendanceService $attendance,
        protected LeaveRequestService $leave,
        protected PayrollRunService $payroll,
        protected EmployeeDocumentService $documents,
        protected PerformanceReviewService $performance,
        protected TrainingAssignmentService $training,
        protected EmployeeExitService $exits,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function overview(int $companyId): array
    {
        $attendance = $this->attendance->dashboardMetrics($companyId);
        $leave = $this->leave->dashboardStats($companyId);
        $payroll = $this->payroll->dashboardStats($companyId);
        $documents = $this->documents->dashboardStats($companyId);
        $performance = $this->performance->dashboardStats($companyId);
        $training = $this->training->dashboardStats($companyId);
        $exit = $this->exits->dashboardStats($companyId);

        $activeEmployees = Employee::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->count();

        return [
            'headline' => [
                'total_employees' => $attendance['total_employees'],
                'active_employees' => $activeEmployees,
                'attendance_percent' => $attendance['attendance_percent'],
                'present_today' => $attendance['present_today'],
                'on_leave_today' => $leave['on_leave_today'],
                'pending_actions' => $leave['pending']
                    + $payroll['pending_approval']
                    + $documents['expiring_soon']
                    + $training['expiring_certificates']
                    + $exit['pending_clearance'],
            ],
            'modules' => [
                [
                    'key' => 'attendance',
                    'label' => __('Attendance'),
                    'description' => __('Time tracking and shift records.'),
                    'route' => 'admin.hr.attendance.dashboard',
                    'permission' => 'hr.attendance.view',
                    'icon' => 'clock',
                    'metrics' => [
                        ['label' => __('Present'), 'value' => $attendance['present_today']],
                        ['label' => __('Absent'), 'value' => $attendance['absent_today']],
                        ['label' => __('Attendance %'), 'value' => $attendance['attendance_percent'].'%'],
                    ],
                ],
                [
                    'key' => 'leave',
                    'label' => __('Leave'),
                    'description' => __('Leave requests and balances.'),
                    'route' => 'admin.hr.leave.dashboard',
                    'permission' => 'hr.leave.view',
                    'icon' => 'calendar',
                    'metrics' => [
                        ['label' => __('Pending'), 'value' => $leave['pending']],
                        ['label' => __('On Leave'), 'value' => $leave['on_leave_today']],
                        ['label' => __('Approved (month)'), 'value' => $leave['approved_this_month']],
                    ],
                ],
                [
                    'key' => 'payroll',
                    'label' => __('Payroll'),
                    'description' => __('Pay runs, payslips, and deductions.'),
                    'route' => 'admin.hr.payroll.dashboard',
                    'permission' => 'hr.payroll.view',
                    'icon' => 'cash',
                    'metrics' => [
                        ['label' => __('Pending Approval'), 'value' => $payroll['pending_approval']],
                        ['label' => __('Posted (year)'), 'value' => $payroll['posted_this_year']],
                        ['label' => __('Last Net Total'), 'value' => number_format($payroll['last_net_total'], 0)],
                    ],
                ],
                [
                    'key' => 'documents',
                    'label' => __('Documents'),
                    'description' => __('HR document repository.'),
                    'route' => 'admin.hr.documents.dashboard',
                    'permission' => 'hr.documents.view',
                    'icon' => 'document-text',
                    'metrics' => [
                        ['label' => __('Total'), 'value' => $documents['total_documents']],
                        ['label' => __('Expiring'), 'value' => $documents['expiring_soon']],
                        ['label' => __('Expired'), 'value' => $documents['expired']],
                    ],
                ],
                [
                    'key' => 'performance',
                    'label' => __('Performance'),
                    'description' => __('Reviews, goals, and appraisals.'),
                    'route' => 'admin.hr.performance.dashboard',
                    'permission' => 'hr.performance.view',
                    'icon' => 'badge-check',
                    'metrics' => [
                        ['label' => __('Reviews (year)'), 'value' => $performance['reviews_this_year']],
                        ['label' => __('Submitted'), 'value' => $performance['submitted']],
                        ['label' => __('Avg Score'), 'value' => $performance['average_score']],
                    ],
                ],
                [
                    'key' => 'training',
                    'label' => __('Training'),
                    'description' => __('Courses, certifications, and development.'),
                    'route' => 'admin.hr.training.dashboard',
                    'permission' => 'hr.training.view',
                    'icon' => 'book-open',
                    'metrics' => [
                        ['label' => __('Active'), 'value' => $training['active_assignments']],
                        ['label' => __('Hours (year)'), 'value' => $training['total_hours']],
                        ['label' => __('Certs Expiring'), 'value' => $training['expiring_certificates']],
                    ],
                ],
                [
                    'key' => 'exit',
                    'label' => __('Exit Management'),
                    'description' => __('Offboarding and clearance workflows.'),
                    'route' => 'admin.hr.exit.dashboard',
                    'permission' => 'hr.exit.view',
                    'icon' => 'switch-horizontal',
                    'metrics' => [
                        ['label' => __('Active Exits'), 'value' => $exit['active_exits']],
                        ['label' => __('Pending Clearance'), 'value' => $exit['pending_clearance']],
                        ['label' => __('Closed (year)'), 'value' => $exit['closed_this_year']],
                    ],
                ],
            ],
            'alerts' => $this->buildAlerts($leave, $payroll, $documents, $training, $exit),
        ];
    }

    /**
     * @return list<array{label: string, value: int, route: string, permission: string}>
     */
    protected function buildAlerts(
        array $leave,
        array $payroll,
        array $documents,
        array $training,
        array $exit,
    ): array {
        $alerts = [];

        if ($leave['pending'] > 0) {
            $alerts[] = [
                'label' => __(':count leave requests pending approval', ['count' => $leave['pending']]),
                'route' => 'admin.hr.leave.index',
                'permission' => 'hr.leave.view',
            ];
        }

        if ($payroll['pending_approval'] > 0) {
            $alerts[] = [
                'label' => __(':count payroll runs awaiting approval', ['count' => $payroll['pending_approval']]),
                'route' => 'admin.hr.payroll.index',
                'permission' => 'hr.payroll.view',
            ];
        }

        if ($documents['expiring_soon'] > 0) {
            $alerts[] = [
                'label' => __(':count HR documents expiring soon', ['count' => $documents['expiring_soon']]),
                'route' => 'admin.hr.documents.dashboard',
                'permission' => 'hr.documents.view',
            ];
        }

        if ($training['expiring_certificates'] > 0) {
            $alerts[] = [
                'label' => __(':count training certificates expiring soon', ['count' => $training['expiring_certificates']]),
                'route' => 'admin.hr.training.dashboard',
                'permission' => 'hr.training.view',
            ];
        }

        if ($exit['pending_clearance'] > 0) {
            $alerts[] = [
                'label' => __(':count exit processes pending clearance', ['count' => $exit['pending_clearance']]),
                'route' => 'admin.hr.exit.index',
                'permission' => 'hr.exit.view',
            ];
        }

        return $alerts;
    }
}

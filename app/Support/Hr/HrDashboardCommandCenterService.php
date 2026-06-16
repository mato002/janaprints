<?php

namespace App\Support\Hr;

use App\Enums\EmployeeDocumentCategory;
use App\Enums\EmploymentStatus;
use App\Enums\ExitStatus;
use App\Enums\LeaveRequestStatus;
use App\Enums\PerformanceRating;
use App\Enums\PerformanceReviewStatus;
use App\Enums\PayrollRunStatus;
use App\Enums\TrainingAssignmentStatus;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hr\AttendanceCorrection;
use App\Models\Hr\EmployeeDocument;
use App\Models\Hr\EmployeeExit;
use App\Models\Hr\EmployeeTrainingAssignment;
use App\Models\Hr\LeaveBalance;
use App\Models\Hr\LeaveRequest;
use App\Models\Hr\PayrollRun;
use App\Models\Hr\PerformanceReview;
use App\Models\Hr\TrainingProgram;
use App\Models\JobTitle;
use App\Models\User;
use App\Support\Reports\HrReportQueries;
use App\Support\Reports\HrReportScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class HrDashboardCommandCenterService
{
    /**
     * @var list<EmployeeDocumentCategory>
     */
    private const MANDATORY_DOCUMENT_CATEGORIES = [
        EmployeeDocumentCategory::Contract,
        EmployeeDocumentCategory::IdCopy,
    ];

    public function __construct(
        protected AttendanceService $attendance,
        protected LeaveRequestService $leave,
        protected PayrollRunService $payroll,
        protected EmployeeDocumentService $documents,
        protected PerformanceReviewService $performance,
        protected TrainingAssignmentService $training,
        protected EmployeeExitService $exits,
        protected HrReportQueries $reportQueries,
        protected HrWorkforceIntelligenceService $workforce,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(int $companyId, ?User $user = null): array
    {
        $user ??= auth()->user();
        $attendance = $this->attendance->dashboardMetrics($companyId);
        $leave = $this->leave->dashboardStats($companyId);
        $payroll = $this->payroll->dashboardStats($companyId);
        $documents = $this->documents->dashboardStats($companyId);
        $performance = $this->performance->dashboardStats($companyId);
        $training = $this->training->dashboardStats($companyId);
        $exit = $this->exits->dashboardStats($companyId);

        $activeEmployees = $this->activeEmployeeCount($companyId);
        $newHiresThisMonth = $this->newHiresCount($companyId, now()->startOfMonth(), now());
        $newHiresLastMonth = $this->newHiresCount(
            $companyId,
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth(),
        );
        $pendingCorrections = $this->pendingAttendanceCorrections($companyId);
        $pendingReviews = $this->pendingPerformanceReviews($companyId);
        $pendingTraining = $this->pendingTrainingApprovals($companyId);
        $pendingDocumentReviews = $this->pendingDocumentReviews($companyId);
        $pendingHrActions = $leave['pending']
            + $payroll['pending_approval']
            + $pendingCorrections
            + $pendingReviews
            + $pendingTraining
            + $exit['pending_clearance']
            + $pendingDocumentReviews;

        $lastMonthAttendance = $this->attendance->dashboardMetrics(
            $companyId,
            Carbon::today()->subMonth(),
        );
        $lastMonthLeave = $this->onLeaveOnDate($companyId, Carbon::today()->subMonth());
        $lastMonthActive = max(0, $activeEmployees - $newHiresThisMonth);
        $lastMonthPendingActions = max(0, (int) round($pendingHrActions * 0.85));

        $monthScope = new HrReportScope(
            $companyId,
            null,
            now()->startOfMonth()->toDateString(),
            now()->toDateString(),
        );
        $overtime = $this->reportQueries->overtimeSummary($monthScope);
        $payrollCost = $this->reportQueries->payrollCostSummary($monthScope);
        $kpiScope = new HrKpiScope(
            $companyId,
            null,
            now()->startOfYear()->toDateString(),
            now()->toDateString(),
        );

        return [
            'as_of' => now()->format('Y-m-d H:i'),
            'overview' => $this->overviewCards($user, [
                'total_employees' => [
                    'value' => $activeEmployees,
                    'previous' => $lastMonthActive,
                    'icon' => 'users',
                    'route' => 'admin.employees.index',
                    'permission' => 'employees.manage',
                ],
                'present_today' => [
                    'value' => $attendance['present_today'],
                    'previous' => $lastMonthAttendance['present_today'],
                    'icon' => 'check-circle',
                    'route' => 'admin.hr.attendance.index',
                    'permission' => 'hr.attendance.view',
                    'query' => ['date' => today()->toDateString()],
                ],
                'on_leave' => [
                    'value' => $leave['on_leave_today'],
                    'previous' => $lastMonthLeave,
                    'icon' => 'calendar',
                    'route' => 'admin.hr.leave.calendar',
                    'permission' => 'hr.leave.view',
                ],
                'absent_today' => [
                    'value' => $attendance['absent_today'],
                    'previous' => $lastMonthAttendance['absent_today'],
                    'icon' => 'x-circle',
                    'route' => 'admin.hr.attendance.index',
                    'permission' => 'hr.attendance.view',
                    'query' => ['date' => today()->toDateString(), 'status' => 'absent'],
                ],
                'pending_hr_actions' => [
                    'value' => $pendingHrActions,
                    'previous' => $lastMonthPendingActions,
                    'icon' => 'exclamation',
                    'route' => 'admin.hr.dashboard',
                    'permission' => 'hr.dashboard.view',
                    'anchor' => '#hr-action-center',
                ],
                'new_hires_month' => [
                    'value' => $newHiresThisMonth,
                    'previous' => $newHiresLastMonth,
                    'icon' => 'user-add',
                    'route' => 'admin.employees.index',
                    'permission' => 'employees.manage',
                ],
            ]),
            'action_center' => $this->actionCenter($user, [
                'leave_approvals' => ['count' => $leave['pending'], 'route' => 'admin.hr.leave.index', 'permission' => 'hr.leave.view', 'query' => ['status' => 'pending']],
                'attendance_corrections' => ['count' => $pendingCorrections, 'route' => 'admin.hr.attendance.index', 'permission' => 'hr.attendance.view'],
                'payroll_approvals' => ['count' => $payroll['pending_approval'], 'route' => 'admin.hr.payroll.index', 'permission' => 'hr.payroll.view'],
                'performance_reviews' => ['count' => $pendingReviews, 'route' => 'admin.hr.performance.index', 'permission' => 'hr.performance.view', 'query' => ['status' => 'draft']],
                'training_approvals' => ['count' => $pendingTraining, 'route' => 'admin.hr.training.assignments.index', 'permission' => 'hr.training.view'],
                'exit_clearances' => ['count' => $exit['pending_clearance'], 'route' => 'admin.hr.exit.index', 'permission' => 'hr.exit.view'],
                'document_reviews' => ['count' => $pendingDocumentReviews, 'route' => 'admin.hr.documents.index', 'permission' => 'hr.documents.view'],
            ]),
            'workforce_distribution' => $this->workforceDistribution($companyId),
            'attendance' => $this->attendanceSnapshot($user, $attendance, $overtime, $companyId),
            'leave' => $this->leaveSnapshot($user, $leave, $companyId),
            'payroll' => $this->payrollSnapshot($user, $payroll, $payrollCost, $companyId),
            'performance' => $this->performanceSnapshot($user, $performance, $companyId, $kpiScope),
            'training' => $this->trainingSnapshot($user, $training, $companyId),
            'document_compliance' => $this->documentComplianceSnapshot($user, $documents, $companyId),
            'exit' => $this->exitSnapshot($user, $exit, $companyId),
            'alerts' => $this->alerts($companyId, $leave, $payroll, $documents, $training, $exit, $pendingDocumentReviews),
            'quick_actions' => $this->quickActions($user),
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $definitions
     * @return list<array<string, mixed>>
     */
    protected function overviewCards(?User $user, array $definitions): array
    {
        $labels = [
            'total_employees' => __('Total Employees'),
            'present_today' => __('Present Today'),
            'on_leave' => __('On Leave'),
            'absent_today' => __('Absent Today'),
            'pending_hr_actions' => __('Pending HR Actions'),
            'new_hires_month' => __('New Hires This Month'),
        ];

        return collect($definitions)->map(function (array $card, string $key) use ($user, $labels) {
            $url = $this->resolveUrl($user, $card);
            $trend = $this->monthOverMonthTrend((float) $card['value'], (float) $card['previous']);

            return [
                'key' => $key,
                'label' => $labels[$key] ?? $key,
                'value' => (string) $card['value'],
                'icon' => $card['icon'],
                'url' => $url,
                'clickable' => $url !== null,
                'trend' => $trend,
            ];
        })->values()->all();
    }

    /**
     * @param  array<string, array<string, mixed>>  $definitions
     * @return list<array<string, mixed>>
     */
    protected function actionCenter(?User $user, array $definitions): array
    {
        $labels = [
            'leave_approvals' => __('Pending Leave Approvals'),
            'attendance_corrections' => __('Pending Attendance Corrections'),
            'payroll_approvals' => __('Pending Payroll Approvals'),
            'performance_reviews' => __('Pending Performance Reviews'),
            'training_approvals' => __('Pending Training Approvals'),
            'exit_clearances' => __('Pending Exit Clearances'),
            'document_reviews' => __('Pending Document Reviews'),
        ];

        return collect($definitions)->map(function (array $item, string $key) use ($user, $labels) {
            $count = (int) $item['count'];
            $url = $this->resolveUrl($user, $item);

            return [
                'key' => $key,
                'label' => $labels[$key] ?? $key,
                'count' => $count,
                'severity' => $this->severity($count),
                'url' => $url,
                'clickable' => $url !== null,
            ];
        })->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function workforceDistribution(int $companyId): array
    {
        $scope = new HrReportScope(
            $companyId,
            null,
            now()->startOfYear()->toDateString(),
            now()->toDateString(),
        );

        return [
            'by_branch' => $this->distributionRows(
                Employee::query()
                    ->where('company_id', $companyId)
                    ->where('is_active', true)
                    ->select('branch_id', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
                    ->groupBy('branch_id')
                    ->orderByDesc('total')
                    ->get()
                    ->map(function ($row) use ($companyId) {
                        $name = Branch::query()->where('company_id', $companyId)->whereKey($row->branch_id)->value('name') ?? __('Unassigned');

                        return ['label' => $name, 'count' => (int) $row->total];
                    })
                    ->all(),
            ),
            'by_department' => $this->distributionRows(
                collect($this->reportQueries->headcountByDepartment($scope))
                    ->map(fn (array $row) => ['label' => $row[0], 'count' => (int) $row[2]])
                    ->all(),
            ),
            'by_employment_type' => $this->distributionRows(
                Employee::query()
                    ->where('company_id', $companyId)
                    ->where('is_active', true)
                    ->select('job_title_id', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
                    ->groupBy('job_title_id')
                    ->orderByDesc('total')
                    ->get()
                    ->map(function ($row) use ($companyId) {
                        $name = JobTitle::query()->where('company_id', $companyId)->whereKey($row->job_title_id)->value('title') ?? __('Unassigned');

                        return ['label' => $name, 'count' => (int) $row->total];
                    })
                    ->all(),
            ),
            'by_status' => $this->distributionRows(
                Employee::query()
                    ->where('company_id', $companyId)
                    ->where('is_active', true)
                    ->select('employment_status', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
                    ->groupBy('employment_status')
                    ->orderByDesc('total')
                    ->get()
                    ->map(function ($row) {
                        $status = $row->employment_status;
                        if (! $status instanceof EmploymentStatus) {
                            $status = EmploymentStatus::tryFrom((string) $status);
                        }

                        return [
                            'label' => $this->employmentStatusLabel($status),
                            'count' => (int) $row->total,
                        ];
                    })
                    ->all(),
            ),
        ];
    }

    /**
     * @param  list<array{label: string, count: int}>  $rows
     * @return list<array{label: string, count: int, percent: int}>
     */
    protected function distributionRows(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        $max = max(1, ...array_column($rows, 'count'));

        return array_map(fn (array $row) => [
            'label' => $row['label'],
            'count' => $row['count'],
            'percent' => (int) round(($row['count'] / $max) * 100),
        ], $rows);
    }

    /**
     * @param  array<string, int|float>  $attendance
     * @param  array<string, float|int>  $overtime
     * @return array<string, mixed>
     */
    protected function attendanceSnapshot(?User $user, array $attendance, array $overtime, int $companyId): array
    {
        $trend = $this->attendanceTrend($companyId, 7);

        return [
            'title' => __('Attendance Snapshot'),
            'open_route' => 'admin.hr.attendance.dashboard',
            'open_permission' => 'hr.attendance.view',
            'open_label' => __('Open Attendance'),
            'open_url' => $this->resolveUrl($user, ['route' => 'admin.hr.attendance.dashboard', 'permission' => 'hr.attendance.view']),
            'metrics' => [
                ['label' => __('Present'), 'value' => (string) $attendance['present_today']],
                ['label' => __('Absent'), 'value' => (string) $attendance['absent_today']],
                ['label' => __('Late Arrivals'), 'value' => (string) $attendance['late_today']],
                ['label' => __('Attendance %'), 'value' => $attendance['attendance_percent'].'%'],
                ['label' => __('Overtime Hours'), 'value' => number_format($overtime['total_hours'], 1)],
            ],
            'trend' => $trend,
        ];
    }

    /**
     * @param  array<string, mixed>  $leave
     * @return array<string, mixed>
     */
    protected function leaveSnapshot(?User $user, array $leave, int $companyId): array
    {
        return [
            'title' => __('Leave Snapshot'),
            'open_route' => 'admin.hr.leave.dashboard',
            'open_permission' => 'hr.leave.view',
            'open_label' => __('Open Leave'),
            'open_url' => $this->resolveUrl($user, ['route' => 'admin.hr.leave.dashboard', 'permission' => 'hr.leave.view']),
            'metrics' => [
                ['label' => __('Employees On Leave'), 'value' => (string) $leave['on_leave_today']],
                ['label' => __('Pending Requests'), 'value' => (string) $leave['pending']],
                ['label' => __('Approved This Month'), 'value' => (string) $leave['approved_this_month']],
                ['label' => __('Leave Liability'), 'value' => number_format($this->leaveLiabilityDays($companyId), 1).' '.__('days')],
                ['label' => __('Upcoming Leave'), 'value' => (string) $this->upcomingLeaveCount($companyId, 14)],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payroll
     * @param  array<string, float|int>  $payrollCost
     * @return array<string, mixed>
     */
    protected function payrollSnapshot(?User $user, array $payroll, array $payrollCost, int $companyId): array
    {
        $lastPosted = PayrollRun::query()
            ->where('company_id', $companyId)
            ->where('status', PayrollRunStatus::Posted->value)
            ->orderByDesc('posted_at')
            ->first();

        $pendingRun = PayrollRun::query()
            ->where('company_id', $companyId)
            ->where('status', PayrollRunStatus::PendingApproval->value)
            ->orderByDesc('processed_at')
            ->first();

        return [
            'title' => __('Payroll Snapshot'),
            'open_route' => 'admin.hr.payroll.dashboard',
            'open_permission' => 'hr.payroll.view',
            'open_label' => __('Open Payroll'),
            'open_url' => $this->resolveUrl($user, ['route' => 'admin.hr.payroll.dashboard', 'permission' => 'hr.payroll.view']),
            'metrics' => [
                ['label' => __('Current Payroll Cost'), 'value' => number_format($payrollCost['net'], 0)],
                ['label' => __('Pending Payroll Runs'), 'value' => (string) $payroll['pending_approval']],
                ['label' => __('Last Posted Payroll'), 'value' => $lastPosted ? number_format((float) $lastPosted->net_total, 0) : '—'],
                ['label' => __('Total Deductions'), 'value' => number_format($payrollCost['deductions'], 0)],
                ['label' => __('Total Net Pay'), 'value' => number_format($payrollCost['net'], 0)],
                ['label' => __('Approval Status'), 'value' => $pendingRun?->status?->label() ?? __('Clear')],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $performance
     * @return array<string, mixed>
     */
    protected function performanceSnapshot(?User $user, array $performance, int $companyId, HrKpiScope $scope): array
    {
        $completed = PerformanceReview::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->where('status', PerformanceReviewStatus::Submitted->value)
            ->whereYear('reviewed_at', now()->year)
            ->count();

        $lowAlerts = PerformanceReview::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->whereIn('rating', [PerformanceRating::Poor->value, PerformanceRating::Critical->value])
            ->whereYear('period_end', now()->year)
            ->count();

        $topDepartment = collect($this->workforce->rankings($scope)['top_departments']['rows'] ?? [])
            ->sortByDesc(fn (array $row) => (float) str_replace('%', '', (string) ($row[1] ?? 0)))
            ->first();

        return [
            'title' => __('Performance Snapshot'),
            'open_route' => 'admin.hr.performance.dashboard',
            'open_permission' => 'hr.performance.view',
            'open_label' => __('Open Performance'),
            'open_url' => $this->resolveUrl($user, ['route' => 'admin.hr.performance.dashboard', 'permission' => 'hr.performance.view']),
            'metrics' => [
                ['label' => __('Pending Reviews'), 'value' => (string) $this->pendingPerformanceReviews($companyId)],
                ['label' => __('Completed Reviews'), 'value' => (string) $completed],
                ['label' => __('Average Rating'), 'value' => (string) $performance['average_score']],
                ['label' => __('Low Performance Alerts'), 'value' => (string) $lowAlerts],
                ['label' => __('Top Department Score'), 'value' => $topDepartment ? $topDepartment[0].' ('.$topDepartment[1].')' : '—'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $training
     * @return array<string, mixed>
     */
    protected function trainingSnapshot(?User $user, array $training, int $companyId): array
    {
        $activePrograms = TrainingProgram::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->count();

        $assignedEmployees = EmployeeTrainingAssignment::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->whereIn('status', [
                TrainingAssignmentStatus::Assigned->value,
                TrainingAssignmentStatus::InProgress->value,
            ])
            ->distinct('employee_id')
            ->count('employee_id');

        return [
            'title' => __('Training Snapshot'),
            'open_route' => 'admin.hr.training.dashboard',
            'open_permission' => 'hr.training.view',
            'open_label' => __('Open Training'),
            'open_url' => $this->resolveUrl($user, ['route' => 'admin.hr.training.dashboard', 'permission' => 'hr.training.view']),
            'metrics' => [
                ['label' => __('Active Programs'), 'value' => (string) $activePrograms],
                ['label' => __('Assigned Employees'), 'value' => (string) $assignedEmployees],
                ['label' => __('Completed Training'), 'value' => (string) $training['completed_this_year']],
                ['label' => __('Expired Certifications'), 'value' => (string) $this->expiredCertifications($companyId)],
                ['label' => __('Upcoming Renewals'), 'value' => (string) $training['expiring_certificates']],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $documents
     * @return array<string, mixed>
     */
    protected function documentComplianceSnapshot(?User $user, array $documents, int $companyId): array
    {
        $byCategory = collect(EmployeeDocumentCategory::cases())
            ->filter(fn (EmployeeDocumentCategory $category) => $category->supportsExpiry())
            ->map(function (EmployeeDocumentCategory $category) use ($companyId) {
                $base = EmployeeDocument::query()
                    ->forTenant()
                    ->where('company_id', $companyId)
                    ->where('is_active', true)
                    ->where('category', $category->value);

                return [
                    'label' => $category->label(),
                    'expiring' => (clone $base)->expiringSoon(30)->count(),
                    'expired' => (clone $base)->expired()->count(),
                ];
            })
            ->filter(fn (array $row) => $row['expiring'] > 0 || $row['expired'] > 0)
            ->values()
            ->all();

        return [
            'title' => __('Document Compliance'),
            'open_route' => 'admin.hr.documents.dashboard',
            'open_permission' => 'hr.documents.view',
            'open_label' => __('Open Documents'),
            'open_url' => $this->resolveUrl($user, ['route' => 'admin.hr.documents.dashboard', 'permission' => 'hr.documents.view']),
            'metrics' => [
                ['label' => __('Expiring 30 Days'), 'value' => (string) $documents['expiring_soon']],
                ['label' => __('Expired Documents'), 'value' => (string) $documents['expired']],
                ['label' => __('Missing Documents'), 'value' => (string) $this->missingMandatoryDocuments($companyId)],
                ['label' => __('Pending Verifications'), 'value' => (string) $this->pendingDocumentReviews($companyId)],
            ],
            'categories' => $byCategory,
        ];
    }

    /**
     * @param  array<string, mixed>  $exit
     * @return array<string, mixed>
     */
    protected function exitSnapshot(?User $user, array $exit, int $companyId): array
    {
        return [
            'title' => __('Exit Management'),
            'open_route' => 'admin.hr.exit.dashboard',
            'open_permission' => 'hr.exit.view',
            'open_label' => __('Open Exit'),
            'open_url' => $this->resolveUrl($user, ['route' => 'admin.hr.exit.dashboard', 'permission' => 'hr.exit.view']),
            'metrics' => [
                ['label' => __('Employees Exiting'), 'value' => (string) $exit['active_exits']],
                ['label' => __('Pending Clearances'), 'value' => (string) $exit['pending_clearance']],
                ['label' => __('Completed Exits'), 'value' => (string) $exit['closed_this_year']],
                ['label' => __('Exit Interviews Pending'), 'value' => (string) $this->exitInterviewsPending($companyId)],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function alerts(
        int $companyId,
        array $leave,
        array $payroll,
        array $documents,
        array $training,
        array $exit,
        int $pendingDocumentReviews,
    ): array {
        $alerts = [];

        if ($documents['expiring_soon'] > 0) {
            $alerts[] = $this->alert(
                __(':count employee documents expire this week', ['count' => min($documents['expiring_soon'], 7)]),
                'high',
                'admin.hr.documents.dashboard',
                'hr.documents.view',
            );
        }

        if ($leave['pending'] > 0) {
            $alerts[] = $this->alert(
                __(':count leave requests pending approval', ['count' => $leave['pending']]),
                'medium',
                'admin.hr.leave.index',
                'hr.leave.view',
            );
        }

        if ($payroll['pending_approval'] > 0) {
            $alerts[] = $this->alert(
                __('Payroll run awaiting approval'),
                'high',
                'admin.hr.payroll.index',
                'hr.payroll.view',
            );
        }

        $missing = $this->missingMandatoryDocuments($companyId);
        if ($missing > 0) {
            $alerts[] = $this->alert(
                __(':count employees missing mandatory documents', ['count' => $missing]),
                'medium',
                'admin.hr.documents.index',
                'hr.documents.view',
            );
        }

        if ($training['expiring_certificates'] > 0) {
            $alerts[] = $this->alert(
                __(':count training certifications expiring soon', ['count' => $training['expiring_certificates']]),
                'low',
                'admin.hr.training.dashboard',
                'hr.training.view',
            );
        }

        if ($exit['pending_clearance'] > 0) {
            $alerts[] = $this->alert(
                __(':count exit clearances pending', ['count' => $exit['pending_clearance']]),
                'medium',
                'admin.hr.exit.index',
                'hr.exit.view',
            );
        }

        if ($pendingDocumentReviews > 0) {
            $alerts[] = $this->alert(
                __(':count documents pending verification', ['count' => $pendingDocumentReviews]),
                'low',
                'admin.hr.documents.index',
                'hr.documents.view',
            );
        }

        return $alerts;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function quickActions(?User $user): array
    {
        $definitions = [
            [
                'label' => __('New Employee'),
                'route' => 'admin.employees.create',
                'permission' => 'employees.manage',
                'modal' => true,
            ],
            [
                'label' => __('New Leave Request'),
                'route' => 'admin.hr.leave.create',
                'permission' => 'hr.leave.create',
                'modal' => true,
            ],
            [
                'label' => __('Run Payroll'),
                'route' => 'admin.hr.payroll.create',
                'permission' => 'hr.payroll.process',
                'modal' => true,
            ],
            [
                'label' => __('Create Training'),
                'route' => 'admin.hr.training.programs.create',
                'permission' => 'hr.training.manage',
                'modal' => true,
            ],
            [
                'label' => __('Start Review'),
                'route' => 'admin.hr.performance.create',
                'permission' => 'hr.performance.manage',
                'modal' => true,
            ],
            [
                'label' => __('Upload Document'),
                'route' => 'admin.hr.documents.create',
                'permission' => 'hr.documents.upload',
                'modal' => true,
            ],
        ];

        return collect($definitions)
            ->filter(function (array $item) use ($user) {
                if (! Route::has($item['route'])) {
                    return false;
                }

                return $user?->can($item['permission']) ?? false;
            })
            ->map(function (array $item) {
                return [
                    'label' => $item['label'],
                    'url' => route($item['route']),
                    'modal' => $item['modal'] ?? false,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array{label: string, value: float, percent: int}>
     */
    protected function attendanceTrend(int $companyId, int $days): array
    {
        $scope = new HrReportScope(
            $companyId,
            null,
            now()->subDays($days - 1)->toDateString(),
            now()->toDateString(),
        );

        $rows = $this->reportQueries->attendanceByDay($scope);
        $points = collect($rows)->map(function (array $row) {
            $records = (int) ($row[1] ?? 0);
            $present = (int) ($row[2] ?? 0);
            $rate = $records > 0 ? round(($present / $records) * 100, 1) : 0.0;

            return [
                'label' => Carbon::parse($row[0])->format('D'),
                'value' => $rate,
            ];
        })->all();

        if ($points === []) {
            return [];
        }

        $max = max(1, ...array_column($points, 'value'));

        return array_map(fn (array $point) => [
            'label' => $point['label'],
            'value' => $point['value'],
            'percent' => (int) round(($point['value'] / $max) * 100),
        ], $points);
    }

    protected function activeEmployeeCount(int $companyId): int
    {
        return Employee::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->count();
    }

    protected function newHiresCount(int $companyId, Carbon $from, Carbon $to): int
    {
        return Employee::query()
            ->where('company_id', $companyId)
            ->whereDate('hire_date', '>=', $from)
            ->whereDate('hire_date', '<=', $to)
            ->count();
    }

    protected function onLeaveOnDate(int $companyId, Carbon $date): int
    {
        if (! Schema::hasTable('leave_requests')) {
            return 0;
        }

        return LeaveRequest::query()
            ->where('company_id', $companyId)
            ->where('status', LeaveRequestStatus::Approved->value)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->count();
    }

    protected function pendingAttendanceCorrections(int $companyId): int
    {
        if (! Schema::hasTable('attendance_corrections')) {
            return 0;
        }

        return AttendanceCorrection::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->whereNull('approved_at')
            ->count();
    }

    protected function pendingPerformanceReviews(int $companyId): int
    {
        if (! Schema::hasTable('performance_reviews')) {
            return 0;
        }

        return PerformanceReview::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->where('status', PerformanceReviewStatus::Draft->value)
            ->count();
    }

    protected function pendingTrainingApprovals(int $companyId): int
    {
        if (! Schema::hasTable('employee_training_assignments')) {
            return 0;
        }

        return EmployeeTrainingAssignment::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->whereIn('status', [
                TrainingAssignmentStatus::Assigned->value,
                TrainingAssignmentStatus::InProgress->value,
            ])
            ->whereDate('due_date', '<', today())
            ->count();
    }

    protected function pendingDocumentReviews(int $companyId): int
    {
        if (! Schema::hasTable('employee_documents')) {
            return 0;
        }

        return EmployeeDocument::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('current_version', '<=', 0)
                    ->orWhere(fn ($inner) => $inner->expiringSoon(7));
            })
            ->count();
    }

    protected function missingMandatoryDocuments(int $companyId): int
    {
        if (! Schema::hasTable('employee_documents')) {
            return 0;
        }

        $activeEmployeeIds = Employee::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->pluck('id');

        $missing = 0;

        foreach ($activeEmployeeIds as $employeeId) {
            foreach (self::MANDATORY_DOCUMENT_CATEGORIES as $category) {
                $hasDocument = EmployeeDocument::query()
                    ->forTenant()
                    ->where('company_id', $companyId)
                    ->where('employee_id', $employeeId)
                    ->where('category', $category->value)
                    ->where('is_active', true)
                    ->exists();

                if (! $hasDocument) {
                    $missing++;
                    break;
                }
            }
        }

        return $missing;
    }

    protected function leaveLiabilityDays(int $companyId): float
    {
        if (! Schema::hasTable('leave_balances')) {
            return 0.0;
        }

        return round((float) LeaveBalance::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->where('balance_year', now()->year)
            ->get()
            ->sum(fn (LeaveBalance $balance) => max(0, $balance->available())), 1);
    }

    protected function upcomingLeaveCount(int $companyId, int $days): int
    {
        if (! Schema::hasTable('leave_requests')) {
            return 0;
        }

        return LeaveRequest::query()
            ->where('company_id', $companyId)
            ->where('status', LeaveRequestStatus::Approved->value)
            ->whereDate('start_date', '>', today())
            ->whereDate('start_date', '<=', today()->addDays($days))
            ->count();
    }

    protected function expiredCertifications(int $companyId): int
    {
        if (! Schema::hasTable('employee_training_assignments')) {
            return 0;
        }

        return EmployeeTrainingAssignment::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->whereNotNull('certificate_expires_at')
            ->whereDate('certificate_expires_at', '<', today())
            ->count();
    }

    protected function exitInterviewsPending(int $companyId): int
    {
        if (! Schema::hasTable('employee_exits')) {
            return 0;
        }

        return EmployeeExit::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->whereIn('status', [
                ExitStatus::Initiated->value,
                ExitStatus::ClearanceInProgress->value,
            ])
            ->count();
    }

    /**
     * @return array{label: string, positive: bool}
     */
    protected function monthOverMonthTrend(float $current, float $previous): array
    {
        if ($previous == 0.0) {
            $delta = $current > 0 ? 100.0 : 0.0;

            return [
                'label' => ($delta >= 0 ? '+' : '').number_format($delta, 1).'% '.__('vs last month'),
                'positive' => $current >= $previous,
            ];
        }

        $delta = round((($current - $previous) / $previous) * 100, 1);

        return [
            'label' => ($delta >= 0 ? '+' : '').$delta.'% '.__('vs last month'),
            'positive' => $delta >= 0,
        ];
    }

    protected function severity(int $count): string
    {
        return match (true) {
            $count >= 10 => 'high',
            $count >= 3 => 'medium',
            $count > 0 => 'low',
            default => 'none',
        };
    }

    protected function employmentStatusLabel(?EmploymentStatus $status): string
    {
        return match ($status) {
            EmploymentStatus::Active => __('Active'),
            EmploymentStatus::OnLeave => __('On Leave'),
            EmploymentStatus::Suspended => __('Suspended'),
            EmploymentStatus::Terminated => __('Terminated'),
            default => __('Unknown'),
        };
    }

    /**
     * @return array{message: string, priority: string, route: string, permission: string, url: string|null}
     */
    protected function alert(string $message, string $priority, string $route, string $permission): array
    {
        $user = auth()->user();
        $url = $user?->can($permission) && Route::has($route) ? route($route) : null;

        return [
            'message' => $message,
            'priority' => $priority,
            'route' => $route,
            'permission' => $permission,
            'url' => $url,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function resolveUrl(?User $user, array $item): ?string
    {
        $permission = $item['permission'] ?? null;
        $route = $item['route'] ?? null;

        if ($route === null || ! Route::has($route)) {
            return null;
        }

        if ($permission !== null && ! ($user?->can($permission) ?? false)) {
            return null;
        }

        $url = route($route, $item['query'] ?? []);

        if (! empty($item['anchor'])) {
            $url .= $item['anchor'];
        }

        return $url;
    }
}

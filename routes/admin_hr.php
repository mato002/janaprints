<?php

use App\Http\Controllers\Admin\Hr\AttendanceController;
use App\Http\Controllers\Admin\Hr\AttendanceDashboardController;
use App\Http\Controllers\Admin\Hr\CompensationAuditController;
use App\Http\Controllers\Admin\Hr\CompensationDashboardController;
use App\Http\Controllers\Admin\Hr\CompensationLibraryController;
use App\Http\Controllers\Admin\Hr\CompensationTemplateController;
use App\Http\Controllers\Admin\Hr\Employee360Controller;
use App\Http\Controllers\Admin\Hr\EmployeeCompensationComponentController;
use App\Http\Controllers\Admin\Hr\EmployeeCompensationController;
use App\Http\Controllers\Admin\Hr\HrDashboardController;
use App\Http\Controllers\Admin\Hr\HrKpiController;
use App\Http\Controllers\Admin\Hr\EmployeeDocumentController;
use App\Http\Controllers\Admin\Hr\EmployeeDocumentDashboardController;
use App\Http\Controllers\Admin\Hr\JobApplicationController;
use App\Http\Controllers\Admin\Hr\JobRequisitionController;
use App\Http\Controllers\Admin\Hr\LeaveConfigurationController;
use App\Http\Controllers\Admin\Hr\LeaveDashboardController;
use App\Http\Controllers\Admin\Hr\LeaveRequestController;
use App\Http\Controllers\Admin\Hr\OnboardingController;
use App\Http\Controllers\Admin\Hr\PayrollDashboardController;
use App\Http\Controllers\Admin\Hr\PayrollGroupDefinitionController;
use App\Http\Controllers\Admin\Hr\PayrollPayslipController;
use App\Http\Controllers\Admin\Hr\PayrollRunController;
use App\Http\Controllers\Admin\Hr\PerformanceDashboardController;
use App\Http\Controllers\Admin\Hr\PerformanceReviewController;
use App\Http\Controllers\Admin\Hr\RecruitmentDashboardController;
use App\Http\Controllers\Admin\Hr\ShiftController;
use App\Http\Controllers\Admin\Hr\EmployeeExitController;
use App\Http\Controllers\Admin\Hr\ExitDashboardController;
use App\Http\Controllers\Admin\Hr\TrainingAssignmentController;
use App\Http\Controllers\Admin\Hr\TrainingCalendarController;
use App\Http\Controllers\Admin\Hr\TrainingCertificatesController;
use App\Http\Controllers\Admin\Hr\TrainingDashboardController;
use App\Http\Controllers\Admin\Hr\TrainingProgramController;
use App\Http\Controllers\Admin\Hr\VacancyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin.auth', 'verified', 'tenant'])
    ->prefix('admin/hr')
    ->name('admin.hr.')
    ->group(function () {
        Route::get('dashboard', HrDashboardController::class)
            ->middleware('permission:hr.dashboard.view')
            ->name('dashboard');

        Route::middleware('permission:employees.manage')->group(function () {
            Route::get('employees/{employee}', [Employee360Controller::class, 'show'])->name('employees.show');
        });

        Route::middleware('permission:hr.compensation.view')->group(function () {
            Route::get('compensation', CompensationDashboardController::class)->name('compensation.dashboard');
            Route::get('compensation/register', [EmployeeCompensationController::class, 'index'])->name('compensation.register');
            Route::get('compensation/employees/{employee}/edit', [EmployeeCompensationController::class, 'edit'])->name('compensation.edit');
            Route::get('compensation/audit', CompensationAuditController::class)->name('compensation.audit');
            Route::get('compensation/payroll-groups', [PayrollGroupDefinitionController::class, 'index'])->name('compensation.payroll-groups');
            Route::get('compensation/templates', [CompensationTemplateController::class, 'index'])->name('compensation.templates');
            Route::get('compensation/allowances', [CompensationLibraryController::class, 'allowances'])->name('compensation.allowances');
            Route::get('compensation/deductions', [CompensationLibraryController::class, 'deductions'])->name('compensation.deductions');
        });

        Route::middleware('permission:hr.compensation.create')->group(function () {
            Route::get('compensation/create', [EmployeeCompensationController::class, 'create'])->name('compensation.create');
            Route::post('compensation', [EmployeeCompensationController::class, 'store'])->name('compensation.store');
            Route::put('compensation/employees/{employee}', [EmployeeCompensationController::class, 'update'])->name('compensation.update');
            Route::get('compensation/templates/create', [CompensationTemplateController::class, 'create'])->name('compensation.templates.create');
            Route::get('compensation/templates/{salaryTemplate}/edit', [CompensationTemplateController::class, 'edit'])->name('compensation.templates.edit');
            Route::post('compensation/templates', [CompensationTemplateController::class, 'store'])->name('compensation.templates.store');
            Route::put('compensation/templates/{salaryTemplate}', [CompensationTemplateController::class, 'update'])->name('compensation.templates.update');
            Route::patch('compensation/templates/{salaryTemplate}/deactivate', [CompensationTemplateController::class, 'deactivate'])->name('compensation.templates.deactivate');
            Route::patch('compensation/templates/{salaryTemplate}/reactivate', [CompensationTemplateController::class, 'reactivate'])->name('compensation.templates.reactivate');
            Route::delete('compensation/templates/{salaryTemplate}', [CompensationTemplateController::class, 'destroy'])->name('compensation.templates.destroy');
            Route::get('compensation/allowances/create', [CompensationLibraryController::class, 'createAllowance'])->name('compensation.allowances.create');
            Route::post('compensation/allowances', [CompensationLibraryController::class, 'storeAllowance'])->name('compensation.allowances.store');
            Route::put('compensation/allowances/{allowanceDefinition}', [CompensationLibraryController::class, 'updateAllowance'])->name('compensation.allowances.update');
            Route::get('compensation/deductions/create', [CompensationLibraryController::class, 'createDeduction'])->name('compensation.deductions.create');
            Route::post('compensation/deductions', [CompensationLibraryController::class, 'storeDeduction'])->name('compensation.deductions.store');
            Route::put('compensation/deductions/{deductionDefinition}', [CompensationLibraryController::class, 'updateDeduction'])->name('compensation.deductions.update');
            Route::post('compensation/employees/{employee}/allowances', [EmployeeCompensationComponentController::class, 'storeAllowance'])->name('compensation.employee.allowances.store');
            Route::delete('compensation/employees/{employee}/allowances/{allowance}', [EmployeeCompensationComponentController::class, 'destroyAllowance'])->name('compensation.employee.allowances.destroy');
            Route::post('compensation/employees/{employee}/deductions', [EmployeeCompensationComponentController::class, 'storeDeduction'])->name('compensation.employee.deductions.store');
            Route::delete('compensation/employees/{employee}/deductions/{deduction}', [EmployeeCompensationComponentController::class, 'destroyDeduction'])->name('compensation.employee.deductions.destroy');
            Route::patch('compensation/payroll-groups/{payrollGroupDefinition}/deactivate', [PayrollGroupDefinitionController::class, 'deactivate'])->name('compensation.payroll-groups.deactivate');
            Route::patch('compensation/payroll-groups/{payrollGroupDefinition}/reactivate', [PayrollGroupDefinitionController::class, 'reactivate'])->name('compensation.payroll-groups.reactivate');
        });

        Route::post('compensation/{compensation}/approve', [EmployeeCompensationController::class, 'approve'])
            ->middleware('permission:hr.compensation.approve')
            ->name('compensation.approve');

        Route::get('attendance', AttendanceDashboardController::class)
            ->middleware('permission:hr.attendance.view')
            ->name('attendance.dashboard');

        Route::middleware('permission:hr.attendance.view')->group(function () {
            Route::get('attendance/register', [AttendanceController::class, 'index'])->name('attendance.index');
            Route::get('shifts', [ShiftController::class, 'index'])->name('shifts.index');
        });

        Route::middleware('permission:hr.attendance.create')->group(function () {
            Route::get('attendance/manual/create', [AttendanceController::class, 'create'])->name('attendance.create');
            Route::post('attendance/manual', [AttendanceController::class, 'store'])->name('attendance.store');
            Route::post('attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
            Route::post('attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
        });

        Route::middleware('permission:hr.attendance.edit')->group(function () {
            Route::get('attendance/{attendanceRecord}/adjust', [AttendanceController::class, 'adjustForm'])->name('attendance.adjust');
            Route::post('attendance/{attendanceRecord}/adjust', [AttendanceController::class, 'adjust'])->name('attendance.adjust.store');
            Route::get('shifts/create', [ShiftController::class, 'create'])->name('shifts.create');
            Route::post('shifts', [ShiftController::class, 'store'])->name('shifts.store');
            Route::get('shifts/{shift}/edit', [ShiftController::class, 'edit'])->name('shifts.edit');
            Route::put('shifts/{shift}', [ShiftController::class, 'update'])->name('shifts.update');
            Route::patch('shifts/{shift}/deactivate', [ShiftController::class, 'deactivate'])->name('shifts.deactivate');
        });

        Route::middleware('permission:hr.attendance.approve')->group(function () {
            Route::post('attendance/corrections/{correction}/approve', [AttendanceController::class, 'approveCorrection'])
                ->name('attendance.corrections.approve');
        });

        Route::post('attendance/export', [AttendanceController::class, 'export'])
            ->middleware('permission:hr.attendance.export')
            ->name('attendance.export');

        Route::get('leave', LeaveDashboardController::class)
            ->middleware('permission:hr.leave.view')
            ->name('leave.dashboard');

        Route::middleware('permission:hr.leave.view')->group(function () {
            Route::get('leave/requests', function (\Illuminate\Http\Request $request) {
                return redirect()->route('admin.hr.leave.dashboard', array_merge(
                    $request->query(),
                    ['tab' => 'requests'],
                ));
            })->name('leave.index');

            Route::get('leave/calendar', function (\Illuminate\Http\Request $request) {
                return redirect()->route('admin.hr.leave.dashboard', array_merge(
                    $request->query(),
                    ['tab' => 'calendar'],
                ));
            })->name('leave.calendar');

            Route::get('leave/balances', function (\Illuminate\Http\Request $request) {
                return redirect()->route('admin.hr.leave.dashboard', array_merge(
                    $request->query(),
                    ['tab' => 'balances'],
                ));
            })->name('leave.balances');
        });

        Route::middleware('permission:hr.leave.create')->group(function () {
            Route::get('leave/requests/create', [LeaveRequestController::class, 'create'])->name('leave.create');
            Route::post('leave/requests', [LeaveRequestController::class, 'store'])->name('leave.store');
            Route::post('leave/requests/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave.cancel');
        });

        Route::middleware('permission:hr.leave.view')->group(function () {
            Route::get('leave/requests/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('leave.show');
        });

        Route::middleware('permission:hr.leave.approve')->group(function () {
            Route::post('leave/requests/{leaveRequest}/approve-supervisor', [LeaveRequestController::class, 'approveSupervisor'])
                ->name('leave.approve.supervisor');
            Route::post('leave/requests/{leaveRequest}/approve-hr', [LeaveRequestController::class, 'approveHr'])
                ->name('leave.approve.hr');
        });

        Route::middleware('permission:hr.leave.reject')->group(function () {
            Route::post('leave/requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])
                ->name('leave.reject');
        });

        Route::post('leave/export', [LeaveRequestController::class, 'export'])
            ->middleware('permission:hr.leave.export')
            ->name('leave.export');

        Route::get('leave/config', function (\Illuminate\Http\Request $request) {
            return redirect()->route('admin.hr.leave.dashboard', array_merge(
                $request->query(),
                ['tab' => 'setup'],
            ));
        })
            ->middleware('permission:hr.leave.config.view')
            ->name('leave.config');

        Route::middleware('permission:hr.leave.config.create')->group(function () {
            Route::post('leave/config/types', [LeaveConfigurationController::class, 'storeLeaveType'])->name('leave.config.types.store');
            Route::post('leave/config/holidays', [LeaveConfigurationController::class, 'storeHoliday'])->name('leave.config.holidays.store');
            Route::post('leave/config/policies', [LeaveConfigurationController::class, 'storePolicy'])->name('leave.config.policies.store');
            Route::post('leave/config/accrual', [LeaveConfigurationController::class, 'storeAccrualRule'])->name('leave.config.accrual.store');
            Route::post('leave/config/carry', [LeaveConfigurationController::class, 'storeCarryForwardRule'])->name('leave.config.carry.store');
        });

        Route::middleware('permission:hr.leave.config.edit')->group(function () {
            Route::put('leave/config/types/{leaveType}', [LeaveConfigurationController::class, 'updateLeaveType'])->name('leave.config.types.update');
            Route::put('leave/config/holidays/{publicHoliday}', [LeaveConfigurationController::class, 'updateHoliday'])->name('leave.config.holidays.update');
            Route::put('leave/config/policies/{leavePolicy}', [LeaveConfigurationController::class, 'updatePolicy'])->name('leave.config.policies.update');
            Route::put('leave/config/accrual/{leaveAccrualRule}', [LeaveConfigurationController::class, 'updateAccrualRule'])->name('leave.config.accrual.update');
            Route::put('leave/config/carry/{leaveCarryForwardRule}', [LeaveConfigurationController::class, 'updateCarryForwardRule'])->name('leave.config.carry.update');
        });

        Route::get('recruitment', RecruitmentDashboardController::class)
            ->middleware('permission:hr.recruitment.view')
            ->name('recruitment.dashboard');

        Route::middleware('permission:hr.recruitment.create')->group(function () {
            Route::get('recruitment/requisitions/create', [JobRequisitionController::class, 'create'])->name('recruitment.requisitions.create');
            Route::post('recruitment/requisitions', [JobRequisitionController::class, 'store'])->name('recruitment.requisitions.store');
            Route::get('recruitment/vacancies/create', [VacancyController::class, 'create'])->name('recruitment.vacancies.create');
            Route::post('recruitment/vacancies', [VacancyController::class, 'store'])->name('recruitment.vacancies.store');
            Route::get('recruitment/applications/create', [JobApplicationController::class, 'create'])->name('recruitment.applications.create');
            Route::post('recruitment/applications', [JobApplicationController::class, 'store'])->name('recruitment.applications.store');
        });

        Route::middleware('permission:hr.recruitment.view')->group(function () {
            Route::get('recruitment/requisitions', [JobRequisitionController::class, 'index'])->name('recruitment.requisitions.index');
            Route::get('recruitment/vacancies', [VacancyController::class, 'index'])->name('recruitment.vacancies.index');
            Route::get('recruitment/applications', [JobApplicationController::class, 'index'])->name('recruitment.applications.index');
            Route::get('recruitment/applications/pipeline', [JobApplicationController::class, 'pipeline'])->name('recruitment.applications.pipeline');
            Route::get('recruitment/requisitions/{jobRequisition}', [JobRequisitionController::class, 'show'])->name('recruitment.requisitions.show');
            Route::get('recruitment/vacancies/{vacancy}', [VacancyController::class, 'show'])->name('recruitment.vacancies.show');
            Route::get('recruitment/applications/{jobApplication}', [JobApplicationController::class, 'show'])->name('recruitment.applications.show');
            Route::get('recruitment/onboarding/{onboardingRecord}', [OnboardingController::class, 'show'])->name('recruitment.onboarding.show');
        });

        Route::middleware('permission:hr.recruitment.manage')->group(function () {
            Route::post('recruitment/requisitions/{jobRequisition}/submit', [JobRequisitionController::class, 'submit'])->name('recruitment.requisitions.submit');
            Route::post('recruitment/requisitions/{jobRequisition}/approve', [JobRequisitionController::class, 'approve'])->name('recruitment.requisitions.approve');
            Route::post('recruitment/vacancies/{vacancy}/publish', [VacancyController::class, 'publish'])->name('recruitment.vacancies.publish');
            Route::post('recruitment/vacancies/{vacancy}/close', [VacancyController::class, 'close'])->name('recruitment.vacancies.close');
            Route::post('recruitment/applications/{jobApplication}/advance', [JobApplicationController::class, 'advance'])->name('recruitment.applications.advance');
            Route::post('recruitment/applications/{jobApplication}/reject', [JobApplicationController::class, 'reject'])->name('recruitment.applications.reject');
            Route::post('recruitment/applications/{jobApplication}/interview', [JobApplicationController::class, 'scheduleInterview'])->name('recruitment.applications.interview');
            Route::post('recruitment/applications/{jobApplication}/feedback', [JobApplicationController::class, 'recordFeedback'])->name('recruitment.applications.feedback');
            Route::post('recruitment/applications/{jobApplication}/offer', [JobApplicationController::class, 'createOffer'])->name('recruitment.applications.offer');
            Route::post('recruitment/offers/{offerLetter}/send', [JobApplicationController::class, 'sendOffer'])->name('recruitment.offers.send');
            Route::post('recruitment/offers/{offerLetter}/accept', [JobApplicationController::class, 'acceptOffer'])->name('recruitment.offers.accept');
            Route::post('recruitment/onboarding/{jobApplication}/start', [OnboardingController::class, 'start'])->name('recruitment.onboarding.start');
            Route::put('recruitment/onboarding/{onboardingRecord}', [OnboardingController::class, 'update'])->name('recruitment.onboarding.update');
            Route::post('recruitment/onboarding/{onboardingRecord}/complete', [OnboardingController::class, 'complete'])->name('recruitment.onboarding.complete');
        });

        Route::get('kpi', [HrKpiController::class, 'index'])
            ->middleware('permission:hr.kpi.view|kpi.view')
            ->name('kpi');

        Route::post('kpi/export', [HrKpiController::class, 'export'])
            ->middleware('permission:hr.kpi.export|reports.export')
            ->name('kpi.export');

        Route::get('payroll', PayrollDashboardController::class)
            ->middleware('permission:hr.payroll.view')
            ->name('payroll.dashboard');

        Route::middleware('permission:hr.payroll.view')->group(function () {
            Route::get('payroll/runs', [PayrollRunController::class, 'index'])->name('payroll.index');
            Route::get('payroll/payslips/{payslip}', [PayrollPayslipController::class, 'show'])->name('payroll.payslip.show');
            Route::get('payroll/payslips/{payslip}/download', [PayrollPayslipController::class, 'download'])->name('payroll.payslip.download');
        });

        Route::middleware('permission:hr.payroll.process')->group(function () {
            Route::get('payroll/runs/create', [PayrollRunController::class, 'create'])->name('payroll.create');
            Route::post('payroll/runs', [PayrollRunController::class, 'store'])->name('payroll.store');
            Route::post('payroll/runs/{payrollRun}/generate', [PayrollRunController::class, 'generate'])->name('payroll.generate');
            Route::post('payroll/runs/{payrollRun}/calculate', [PayrollRunController::class, 'calculate'])->name('payroll.calculate');
            Route::post('payroll/runs/{payrollRun}/submit-review', [PayrollRunController::class, 'submitReview'])->name('payroll.submit-review');
            Route::post('payroll/runs/{payrollRun}/submit-approval', [PayrollRunController::class, 'submitApproval'])->name('payroll.submit-approval');
            Route::post('payroll/runs/{payrollRun}/cancel', [PayrollRunController::class, 'cancel'])->name('payroll.cancel');
            Route::post('payroll/payslips/{payslip}/email', [PayrollPayslipController::class, 'email'])->name('payroll.payslip.email');
            Route::post('payroll/runs/{payrollRun}/email-payslips', [PayrollRunController::class, 'emailPayslips'])->name('payroll.email-payslips');
        });

        Route::middleware('permission:hr.payroll.view')->group(function () {
            Route::get('payroll/runs/{payrollRun}', [PayrollRunController::class, 'show'])->name('payroll.show');
        });

        Route::middleware('permission:hr.payroll.approve')->group(function () {
            Route::post('payroll/runs/{payrollRun}/approve', [PayrollRunController::class, 'approve'])->name('payroll.approve');
            Route::post('payroll/runs/{payrollRun}/reject', [PayrollRunController::class, 'reject'])->name('payroll.reject');
        });

        Route::middleware('permission:hr.payroll.post')->group(function () {
            Route::post('payroll/runs/{payrollRun}/post', [PayrollRunController::class, 'post'])->name('payroll.post');
        });

        Route::middleware('permission:hr.payroll.release')->group(function () {
            Route::post('payroll/runs/{payrollRun}/release-payslips', [PayrollRunController::class, 'releasePayslips'])->name('payroll.release-payslips');
        });

        Route::middleware('permission:hr.payroll.mark-paid')->group(function () {
            Route::post('payroll/runs/{payrollRun}/mark-paid', [PayrollRunController::class, 'markPaid'])->name('payroll.mark-paid');
        });

        Route::get('payroll/runs/{payrollRun}/export', [PayrollRunController::class, 'export'])
            ->middleware('permission:hr.payroll.export')
            ->name('payroll.export');

        Route::get('payroll/runs/{payrollRun}/export-payment', [PayrollRunController::class, 'exportPayment'])
            ->middleware('permission:hr.payroll.export')
            ->name('payroll.export-payment');

        Route::get('documents', EmployeeDocumentDashboardController::class)
            ->middleware('permission:hr.documents.view')
            ->name('documents.dashboard');

        Route::middleware('permission:hr.documents.view')->group(function () {
            Route::get('documents/list', [EmployeeDocumentController::class, 'index'])->name('documents.index');
        });

        Route::middleware('permission:hr.documents.upload')->group(function () {
            Route::get('documents/upload/create', [EmployeeDocumentController::class, 'create'])->name('documents.create');
            Route::post('documents/upload', [EmployeeDocumentController::class, 'store'])->name('documents.store');
            Route::post('documents/{employeeDocument}/versions', [EmployeeDocumentController::class, 'uploadVersion'])->name('documents.upload');
        });

        Route::middleware('permission:hr.documents.view')->group(function () {
            Route::get('documents/{employeeDocument}', [EmployeeDocumentController::class, 'show'])->name('documents.show');
            Route::get('documents/{employeeDocument}/download', [EmployeeDocumentController::class, 'download'])->name('documents.download');
            Route::get('documents/{employeeDocument}/versions/{employeeDocumentVersion}/download', [EmployeeDocumentController::class, 'downloadVersion'])
                ->name('documents.version.download');
        });

        Route::delete('documents/{employeeDocument}', [EmployeeDocumentController::class, 'destroy'])
            ->middleware('permission:hr.documents.delete')
            ->name('documents.destroy');

        Route::get('performance', PerformanceDashboardController::class)
            ->middleware('permission:hr.performance.view')
            ->name('performance.dashboard');

        Route::middleware('permission:hr.performance.manage')->group(function () {
            Route::get('performance/reviews/create', [PerformanceReviewController::class, 'create'])->name('performance.create');
            Route::post('performance/reviews', [PerformanceReviewController::class, 'store'])->name('performance.store');
            Route::post('performance/reviews/preview-kpis', [PerformanceReviewController::class, 'previewKpis'])->name('performance.preview-kpis');
        });

        Route::middleware('permission:hr.performance.view')->group(function () {
            Route::get('performance/reviews', [PerformanceReviewController::class, 'index'])->name('performance.index');
        });

        Route::middleware('permission:hr.performance.view')->group(function () {
            Route::get('performance/reviews/{performanceReview}', [PerformanceReviewController::class, 'show'])->name('performance.show');
        });

        Route::middleware('permission:hr.performance.manage')->group(function () {
            Route::post('performance/reviews/{performanceReview}/submit', [PerformanceReviewController::class, 'submit'])->name('performance.submit');
            Route::delete('performance/reviews/{performanceReview}', [PerformanceReviewController::class, 'destroy'])->name('performance.destroy');
        });

        Route::get('training', TrainingDashboardController::class)
            ->middleware('permission:hr.training.view')
            ->name('training.dashboard');

        Route::middleware('permission:hr.training.view')->group(function () {
            Route::get('training/programs', [TrainingProgramController::class, 'index'])->name('training.programs.index');
            Route::get('training/assignments', [TrainingAssignmentController::class, 'index'])->name('training.assignments.index');
            Route::get('training/skills-matrix', [TrainingAssignmentController::class, 'skillsMatrix'])->name('training.skills-matrix');
            Route::get('training/calendar', [TrainingCalendarController::class, 'index'])->name('training.calendar');
            Route::get('training/certificates', [TrainingCertificatesController::class, 'index'])->name('training.certificates');
        });

        Route::middleware('permission:hr.training.manage')->group(function () {
            Route::get('training/programs/create', [TrainingProgramController::class, 'create'])->name('training.programs.create');
            Route::post('training/programs', [TrainingProgramController::class, 'store'])->name('training.programs.store');
            Route::get('training/assignments/create', [TrainingAssignmentController::class, 'create'])->name('training.assignments.create');
            Route::post('training/assignments', [TrainingAssignmentController::class, 'store'])->name('training.assignments.store');
        });

        Route::middleware('permission:hr.training.view')->group(function () {
            Route::get('training/programs/{program}', [TrainingProgramController::class, 'show'])->name('training.programs.show');
            Route::get('training/assignments/{employeeTrainingAssignment}', [TrainingAssignmentController::class, 'show'])->name('training.assignments.show');
        });

        Route::middleware('permission:hr.training.manage')->group(function () {
            Route::get('training/programs/{program}/edit', [TrainingProgramController::class, 'edit'])->name('training.programs.edit');
            Route::put('training/programs/{program}', [TrainingProgramController::class, 'update'])->name('training.programs.update');
            Route::post('training/programs/{program}/activate', [TrainingProgramController::class, 'activate'])->name('training.programs.activate');
            Route::post('training/programs/{program}/deactivate', [TrainingProgramController::class, 'deactivate'])->name('training.programs.deactivate');
            Route::post('training/programs/{program}/complete', [TrainingProgramController::class, 'complete'])->name('training.programs.complete');
            Route::post('training/programs/{program}/reopen', [TrainingProgramController::class, 'reopen'])->name('training.programs.reopen');
            Route::post('training/programs/{program}/duplicate', [TrainingProgramController::class, 'duplicate'])->name('training.programs.duplicate');
            Route::post('training/programs/{program}/archive', [TrainingProgramController::class, 'archive'])->name('training.programs.archive');
            Route::post('training/programs/{program}/evaluate', [TrainingProgramController::class, 'evaluate'])->name('training.programs.evaluate');
            Route::post('training/assignments/{employeeTrainingAssignment}/start', [TrainingAssignmentController::class, 'start'])->name('training.assignments.start');
            Route::post('training/assignments/{employeeTrainingAssignment}/cancel', [TrainingAssignmentController::class, 'cancel'])->name('training.assignments.cancel');
        });

        Route::post('training/assignments/{employeeTrainingAssignment}/complete', [TrainingAssignmentController::class, 'complete'])
            ->middleware('permission:hr.training.manage')
            ->name('training.assignments.complete');

        Route::get('exit', ExitDashboardController::class)
            ->middleware('permission:hr.exit.view')
            ->name('exit.dashboard');

        Route::middleware('permission:hr.exit.manage')->group(function () {
            Route::get('exit/processes/create', [EmployeeExitController::class, 'create'])->name('exit.create');
            Route::post('exit/processes', [EmployeeExitController::class, 'store'])->name('exit.store');
        });

        Route::middleware('permission:hr.exit.view')->group(function () {
            Route::get('exit/processes', [EmployeeExitController::class, 'index'])->name('exit.index');
        });

        Route::middleware('permission:hr.exit.view')->group(function () {
            Route::get('exit/processes/{employeeExit}', [EmployeeExitController::class, 'show'])->name('exit.show');
        });

        Route::middleware('permission:hr.exit.manage')->group(function () {
            Route::post('exit/processes/{employeeExit}/clearances/{employeeExitClearance}', [EmployeeExitController::class, 'updateClearance'])
                ->name('exit.clearance');
            Route::post('exit/processes/{employeeExit}/settle', [EmployeeExitController::class, 'settle'])->name('exit.settle');
            Route::post('exit/processes/{employeeExit}/close', [EmployeeExitController::class, 'close'])->name('exit.close');
        });
    });

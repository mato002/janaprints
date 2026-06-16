<?php

use App\Http\Controllers\Admin\Hr\AttendanceController;
use App\Http\Controllers\Admin\Hr\AttendanceDashboardController;
use App\Http\Controllers\Admin\Hr\HrDashboardController;
use App\Http\Controllers\Admin\Hr\EmployeeDocumentController;
use App\Http\Controllers\Admin\Hr\EmployeeDocumentDashboardController;
use App\Http\Controllers\Admin\Hr\LeaveBalanceController;
use App\Http\Controllers\Admin\Hr\LeaveCalendarController;
use App\Http\Controllers\Admin\Hr\LeaveDashboardController;
use App\Http\Controllers\Admin\Hr\LeaveRequestController;
use App\Http\Controllers\Admin\Hr\PayrollDashboardController;
use App\Http\Controllers\Admin\Hr\PayrollPayslipController;
use App\Http\Controllers\Admin\Hr\PayrollRunController;
use App\Http\Controllers\Admin\Hr\PerformanceDashboardController;
use App\Http\Controllers\Admin\Hr\PerformanceReviewController;
use App\Http\Controllers\Admin\Hr\ShiftController;
use App\Http\Controllers\Admin\Hr\EmployeeExitController;
use App\Http\Controllers\Admin\Hr\ExitDashboardController;
use App\Http\Controllers\Admin\Hr\TrainingAssignmentController;
use App\Http\Controllers\Admin\Hr\TrainingCalendarController;
use App\Http\Controllers\Admin\Hr\TrainingCertificatesController;
use App\Http\Controllers\Admin\Hr\TrainingDashboardController;
use App\Http\Controllers\Admin\Hr\TrainingProgramController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin.auth', 'verified', 'tenant'])
    ->prefix('admin/hr')
    ->name('admin.hr.')
    ->group(function () {
        Route::get('dashboard', HrDashboardController::class)
            ->middleware('permission:hr.dashboard.view')
            ->name('dashboard');

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
            Route::get('leave/requests', [LeaveRequestController::class, 'index'])->name('leave.index');
            Route::get('leave/calendar', [LeaveCalendarController::class, 'index'])->name('leave.calendar');
            Route::get('leave/balances', [LeaveBalanceController::class, 'index'])->name('leave.balances');
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
        });

        Route::middleware('permission:hr.payroll.view')->group(function () {
            Route::get('payroll/runs/{payrollRun}', [PayrollRunController::class, 'show'])->name('payroll.show');
        });

        Route::middleware('permission:hr.payroll.approve')->group(function () {
            Route::post('payroll/runs/{payrollRun}/approve', [PayrollRunController::class, 'approve'])->name('payroll.approve');
            Route::post('payroll/runs/{payrollRun}/reject', [PayrollRunController::class, 'reject'])->name('payroll.reject');
            Route::post('payroll/runs/{payrollRun}/post', [PayrollRunController::class, 'post'])->name('payroll.post');
            Route::post('payroll/runs/{payrollRun}/release-payslips', [PayrollRunController::class, 'releasePayslips'])->name('payroll.release-payslips');
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

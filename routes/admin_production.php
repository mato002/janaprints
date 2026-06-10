<?php

use App\Http\Controllers\Admin\Production\CostingReportController;
use App\Http\Controllers\Admin\Production\JobCostingController;
use App\Http\Controllers\Admin\Production\ProductionDashboardController;
use App\Http\Controllers\Admin\Production\ProductionJobCardController;
use App\Http\Controllers\Admin\Production\ProductionOperationController;
use App\Http\Controllers\Admin\Production\ProductionQualityController;
use App\Http\Controllers\Admin\Production\ProductionQueueController;
use App\Http\Controllers\Admin\Production\ProductionSchedulingController;
use App\Http\Controllers\Admin\Production\QualityCheckController;
use App\Http\Controllers\Admin\Production\WorkCenterController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/production')
    ->name('admin.production.')
    ->group(function () {
        Route::get('/', ProductionDashboardController::class)
            ->middleware('permission:production.view')
            ->name('dashboard');

        Route::middleware('permission:production.view')->group(function () {
            Route::get('job-cards', [ProductionJobCardController::class, 'index'])->name('job-cards.index');
            Route::get('job-cards/export', [ProductionJobCardController::class, 'export'])->name('job-cards.export');
        });

        Route::middleware('permission:production.work-centers.view')->group(function () {
            Route::get('work-centers', [WorkCenterController::class, 'index'])->name('work-centers.index');
            Route::get('work-centers/{workCenter}', [WorkCenterController::class, 'show'])->name('work-centers.show');
        });

        Route::middleware('permission:production.queue.view')->group(function () {
            Route::get('queue', [ProductionQueueController::class, 'index'])->name('queue.index');
        });

        Route::middleware('permission:production.scheduling.view')->group(function () {
            Route::get('scheduling', [ProductionSchedulingController::class, 'index'])->name('scheduling.index');
            Route::get('scheduling/export', [ProductionSchedulingController::class, 'export'])->name('scheduling.export');
        });

        Route::middleware('permission:production.quality.view')->group(function () {
            Route::get('quality', [ProductionQualityController::class, 'index'])->name('quality.index');
        });

        Route::middleware('permission:production.create')->group(function () {
            Route::get('job-cards/create', [ProductionJobCardController::class, 'create'])->name('job-cards.create');
            Route::post('job-cards', [ProductionJobCardController::class, 'store'])->name('job-cards.store');
        });

        Route::middleware('permission:production.view')->group(function () {
            Route::get('job-cards/{jobCard}', [ProductionJobCardController::class, 'show'])->name('job-cards.show');
        });

        Route::middleware('permission:production.costing.view')->group(function () {
            Route::get('costing', [JobCostingController::class, 'dashboard'])->name('costing.dashboard');
            Route::get('job-cards/{jobCard}/costing', [JobCostingController::class, 'show'])->name('job-cards.costing');
        });

        Route::middleware([\App\Http\Middleware\CaptureWorkspaceNavigationQuery::class])->group(function () {
            Route::get('reports', [CostingReportController::class, 'index'])
                ->middleware('permission:reports.costing.view')
                ->name('reports.index');

            Route::post('reports/export', [CostingReportController::class, 'export'])
                ->middleware('permission:reports.costing.export')
                ->name('reports.export');
        });

        Route::middleware('permission:production.edit')->group(function () {
            Route::get('job-cards/{jobCard}/edit', [ProductionJobCardController::class, 'edit'])->name('job-cards.edit');
            Route::put('job-cards/{jobCard}', [ProductionJobCardController::class, 'update'])->name('job-cards.update');
            Route::post('job-cards/{jobCard}/hold', [ProductionJobCardController::class, 'hold'])->name('job-cards.hold');
            Route::post('job-cards/{jobCard}/cancel', [ProductionJobCardController::class, 'cancel'])->name('job-cards.cancel');
        });

        Route::middleware('permission:machines.assign')->group(function () {
            Route::post('job-cards/{jobCard}/assign-machine', [ProductionJobCardController::class, 'assignMachine'])
                ->name('job-cards.assign-machine');
        });

        Route::middleware('permission:production.delete')->group(function () {
            Route::delete('job-cards/{jobCard}', [ProductionJobCardController::class, 'destroy'])->name('job-cards.destroy');
        });

        Route::middleware('permission:production.schedule')->group(function () {
            Route::post('job-cards/{jobCard}/schedule', [ProductionJobCardController::class, 'schedule'])->name('job-cards.schedule');
            Route::post('job-cards/{jobCard}/queue', [ProductionJobCardController::class, 'queue'])->name('job-cards.queue');
            Route::post('job-cards/{jobCard}/queues', [ProductionQueueController::class, 'store'])->name('queues.store');
            Route::put('job-cards/{jobCard}/queues/{queue}', [ProductionQueueController::class, 'update'])->name('queues.update');
            Route::delete('job-cards/{jobCard}/queues/{queue}', [ProductionQueueController::class, 'destroy'])->name('queues.destroy');
        });

        Route::middleware('permission:production.start')->group(function () {
            Route::post('job-cards/{jobCard}/start', [ProductionJobCardController::class, 'start'])->name('job-cards.start');
            Route::post('job-cards/{jobCard}/operations', [ProductionOperationController::class, 'store'])->name('operations.store');
            Route::put('job-cards/{jobCard}/operations/{operation}', [ProductionOperationController::class, 'update'])->name('operations.update');
        });

        Route::middleware('permission:production.complete')->group(function () {
            Route::post('job-cards/{jobCard}/send-to-qc', [ProductionJobCardController::class, 'sendToQc'])->name('job-cards.send-to-qc');
            Route::post('job-cards/{jobCard}/complete', [ProductionJobCardController::class, 'markCompleted'])->name('job-cards.complete');
            Route::post('job-cards/{jobCard}/ready-for-dispatch', [ProductionJobCardController::class, 'readyForDispatch'])->name('job-cards.ready-for-dispatch');
            Route::post('job-cards/{jobCard}/operations/{operation}/complete', [ProductionOperationController::class, 'complete'])->name('operations.complete');
        });

        Route::middleware('permission:production.qc')->group(function () {
            Route::post('job-cards/{jobCard}/quality-checks', [QualityCheckController::class, 'store'])->name('quality-checks.store');
        });
    });

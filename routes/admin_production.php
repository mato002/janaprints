<?php

use App\Http\Controllers\Admin\Production\CostingReportController;
use App\Http\Controllers\Admin\Production\JobCostingController;
use App\Http\Controllers\Admin\Production\ProductionDashboardController;
use App\Http\Controllers\Admin\Production\ProductionFloorController;
use App\Http\Controllers\Admin\Production\ProductionGovernanceController;
use App\Http\Controllers\Admin\Production\ProductionOutputController;
use App\Http\Controllers\Admin\Production\ProductionJobCardController;
use App\Http\Controllers\Admin\Production\ProductionOperationController;
use App\Http\Controllers\Admin\Production\ProductionJobCardScanController;
use App\Http\Controllers\Admin\Production\ProductionQueueController;
use App\Http\Controllers\Admin\Production\ProductionSchedulingController;
use App\Http\Controllers\Admin\Production\QualityCheckController;
use App\Http\Controllers\Admin\Production\ProductionMaterialIssueController;
use App\Http\Controllers\Admin\Production\ProductionMaterialRequirementController;
use App\Http\Controllers\Admin\Production\PrintProductTemplateController;
use App\Http\Controllers\Admin\Production\ProductBomController;
use App\Http\Controllers\Admin\Production\ProductionWastageController;
use App\Http\Controllers\Admin\Production\WorkCenterController;
use App\Http\Controllers\Admin\Production\WorkCenterSetupController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/production')
    ->name('admin.production.')
    ->group(function () {
        Route::get('/', [ProductionFloorController::class, 'index'])
            ->middleware('permission:production.view')
            ->name('home');

        Route::middleware('permission:production.view')->group(function () {
            Route::get('floor', [ProductionFloorController::class, 'index'])->name('floor');
            Route::get('floor/jobs/{jobCard}/panel', [ProductionFloorController::class, 'panel'])->name('floor.panel');
            Route::get('scan/{code}', [ProductionJobCardScanController::class, 'scan'])->name('scan.show');
        });

        Route::middleware('permission:machines.assign')->group(function () {
            Route::post('floor/jobs/{jobCard}/assign-machine', [ProductionFloorController::class, 'assignMachine'])
                ->name('floor.assign-machine');
        });

        Route::middleware('permission:production.qc')->group(function () {
            Route::post('floor/jobs/{jobCard}/quick-pass-qc', [ProductionFloorController::class, 'quickPassQc'])
                ->name('floor.quick-pass-qc');
        });

        Route::get('command-center', ProductionDashboardController::class)
            ->middleware('permission:production.view')
            ->name('dashboard');

        Route::middleware('permission:production.view')->group(function () {
            Route::get('job-cards', [ProductionJobCardController::class, 'index'])->name('job-cards.index');
            Route::get('job-cards/export', [ProductionJobCardController::class, 'export'])->name('job-cards.export');
            Route::get('job-cards/{jobCard}/label', [ProductionJobCardScanController::class, 'label'])->name('job-cards.label');
        });

        Route::middleware('permission:production.work-centers.view')->group(function () {
            Route::get('work-centers', [WorkCenterController::class, 'index'])->name('work-centers.index');
            Route::get('work-centers/{workCenter}', [WorkCenterController::class, 'show'])->name('work-centers.show');
        });

        Route::middleware('permission:production.edit')->group(function () {
            Route::get('work-centers/setup/create', [WorkCenterSetupController::class, 'create'])->name('work-centers.create');
            Route::post('work-centers/setup', [WorkCenterSetupController::class, 'store'])->name('work-centers.store');
            Route::get('work-centers/setup/{workCenter}/edit', [WorkCenterSetupController::class, 'edit'])->name('work-centers.edit');
            Route::put('work-centers/setup/{workCenter}', [WorkCenterSetupController::class, 'update'])->name('work-centers.update');
        });

        Route::middleware('permission:production.queue.view')->group(function () {
            Route::get('queue/export', [ProductionQueueController::class, 'export'])->name('queue.export');
            Route::get('queue/department/{department}', [ProductionQueueController::class, 'department'])->name('queue.department');
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
            Route::get('job-cards/{jobCard}/floor-display', [ProductionGovernanceController::class, 'floorDisplay'])->name('job-cards.floor-display');
            Route::get('outputs', [ProductionOutputController::class, 'index'])->name('outputs.index');
        });

        Route::middleware('permission:production.outputs.post')->group(function () {
            Route::post('job-cards/{jobCard}/outputs', [ProductionOutputController::class, 'store'])->name('job-cards.outputs.store');
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
            Route::post('job-cards/{jobCard}/pause', [ProductionJobCardController::class, 'pause'])->name('job-cards.pause');
            Route::post('job-cards/{jobCard}/resume', [ProductionJobCardController::class, 'resume'])->name('job-cards.resume');
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
            Route::put('job-cards/{jobCard}/route-steps/{step}', [ProductionGovernanceController::class, 'updateRouteStep'])->name('job-cards.route-steps.update');
            Route::post('job-cards/{jobCard}/sessions', [ProductionGovernanceController::class, 'storeSession'])->name('job-cards.sessions.store');
        });

        Route::middleware('permission:production.complete')->group(function () {
            Route::post('job-cards/{jobCard}/send-to-qc', [ProductionJobCardController::class, 'sendToQc'])->name('job-cards.send-to-qc');
            Route::post('job-cards/{jobCard}/complete', [ProductionJobCardController::class, 'markCompleted'])->name('job-cards.complete');
            Route::post('job-cards/{jobCard}/ready-for-dispatch', [ProductionJobCardController::class, 'readyForDispatch'])->name('job-cards.ready-for-dispatch');
            Route::post('job-cards/{jobCard}/operations/{operation}/complete', [ProductionOperationController::class, 'complete'])->name('operations.complete');
            Route::post('job-cards/{jobCard}/serials/confirm', [ProductionGovernanceController::class, 'confirmSerials'])->name('job-cards.serials.confirm');
        });

        Route::middleware('permission:production.edit')->group(function () {
            Route::post('job-cards/{jobCard}/outsource', [ProductionGovernanceController::class, 'outsource'])->name('job-cards.outsource');
            Route::post('job-cards/{jobCard}/outsource/return', [ProductionGovernanceController::class, 'markReturned'])->name('job-cards.outsource.return');
        });

        Route::middleware('permission:production.qc')->group(function () {
            Route::post('job-cards/{jobCard}/quality-checks', [QualityCheckController::class, 'store'])->name('quality-checks.store');
            Route::post('job-cards/{jobCard}/quality-checks/{qualityCheck}/approve-customer', [QualityCheckController::class, 'approveCustomer'])->name('quality-checks.approve-customer');
        });

        Route::middleware('permission:production.bom.view')->group(function () {
            Route::get('boms', [ProductBomController::class, 'index'])->name('boms.index');
        });

        Route::middleware('permission:production.bom.create')->group(function () {
            Route::get('boms/create', [ProductBomController::class, 'create'])->name('boms.create');
            Route::post('boms', [ProductBomController::class, 'store'])->name('boms.store');
        });

        Route::middleware('permission:production.bom.view')->group(function () {
            Route::get('boms/{bom}', [ProductBomController::class, 'show'])->name('boms.show');
        });

        Route::middleware('permission:production.bom.edit')->group(function () {
            Route::get('boms/{bom}/edit', [ProductBomController::class, 'edit'])->name('boms.edit');
            Route::put('boms/{bom}', [ProductBomController::class, 'update'])->name('boms.update');
        });

        Route::middleware('permission:production.bom.edit')->group(function () {
            Route::delete('boms/{bom}', [ProductBomController::class, 'destroy'])->name('boms.destroy');
        });

        Route::middleware('permission:production.bom.view')->group(function () {
            Route::get('print-templates', [PrintProductTemplateController::class, 'index'])->name('print-templates.index');
            Route::get('print-templates/export', [PrintProductTemplateController::class, 'export'])->name('print-templates.export');
        });

        Route::middleware('permission:production.bom.create')->group(function () {
            Route::get('print-templates/create', [PrintProductTemplateController::class, 'create'])->name('print-templates.create');
            Route::post('print-templates', [PrintProductTemplateController::class, 'store'])->name('print-templates.store');
        });

        Route::middleware('permission:production.bom.view')->group(function () {
            Route::get('print-templates/{printTemplate}', [PrintProductTemplateController::class, 'show'])->name('print-templates.show');
        });

        Route::middleware('permission:production.bom.create')->group(function () {
            Route::post('print-templates/{printTemplate}/duplicate', [PrintProductTemplateController::class, 'duplicate'])->name('print-templates.duplicate');
        });

        Route::middleware('permission:production.bom.edit')->group(function () {
            Route::get('print-templates/{printTemplate}/edit', [PrintProductTemplateController::class, 'edit'])->name('print-templates.edit');
            Route::put('print-templates/{printTemplate}', [PrintProductTemplateController::class, 'update'])->name('print-templates.update');
            Route::post('print-templates/{printTemplate}/toggle-active', [PrintProductTemplateController::class, 'toggleActive'])->name('print-templates.toggle-active');
        });

        Route::middleware('permission:production.materials.generate')->group(function () {
            Route::post('job-cards/{jobCard}/materials/generate', [ProductionMaterialRequirementController::class, 'generate'])->name('job-cards.materials.generate');
        });

        Route::middleware('permission:production.materials.reserve')->group(function () {
            Route::post('job-cards/{jobCard}/materials/reserve-all', [ProductionMaterialRequirementController::class, 'reserveAll'])->name('job-cards.materials.reserve-all');
            Route::post('job-cards/{jobCard}/materials/{requirement}/reserve', [ProductionMaterialRequirementController::class, 'reserve'])->name('job-cards.materials.reserve');
        });

        Route::middleware('permission:production.materials.issue')->group(function () {
            Route::post('job-cards/{jobCard}/materials/issue-all', [ProductionMaterialIssueController::class, 'storeAll'])->name('job-cards.materials.issue-all');
            Route::post('job-cards/{jobCard}/materials/{requirement}/issue', [ProductionMaterialIssueController::class, 'store'])->name('job-cards.materials.issue');
        });

        Route::middleware('permission:production.materials.consume')->group(function () {
            Route::post('job-cards/{jobCard}/materials/{requirement}/consume', [ProductionMaterialRequirementController::class, 'consume'])->name('job-cards.materials.consume');
        });

        Route::middleware('permission:production.wastage.record')->group(function () {
            Route::post('job-cards/{jobCard}/wastage', [ProductionWastageController::class, 'store'])->name('job-cards.wastage.store');
        });

        Route::middleware('permission:production.complete')->group(function () {
            Route::post('job-cards/{jobCard}/fulfilment/ready-for-collection', [\App\Http\Controllers\Admin\Production\ProductionFulfilmentController::class, 'markReadyForCollection'])
                ->name('job-cards.fulfilment.ready-for-collection');
            Route::post('job-cards/{jobCard}/fulfilment/{fulfilment}/confirm-collection', [\App\Http\Controllers\Admin\Production\ProductionFulfilmentController::class, 'confirmCollection'])
                ->name('job-cards.fulfilment.confirm-collection');
            Route::post('job-cards/{jobCard}/fulfilment/delivery', [\App\Http\Controllers\Admin\Production\ProductionFulfilmentController::class, 'createDelivery'])
                ->name('job-cards.fulfilment.create-delivery');
            Route::post('job-cards/{jobCard}/fulfilment/{fulfilment}/prepare-delivery', [\App\Http\Controllers\Admin\Production\ProductionFulfilmentController::class, 'prepareDelivery'])
                ->name('job-cards.fulfilment.prepare-delivery');
            Route::post('job-cards/{jobCard}/fulfilment/{fulfilment}/confirm-delivery', [\App\Http\Controllers\Admin\Production\ProductionFulfilmentController::class, 'confirmDelivery'])
                ->name('job-cards.fulfilment.confirm-delivery');
        });
    });

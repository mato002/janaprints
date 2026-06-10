<?php

use App\Http\Controllers\Admin\Assets\AssetBulkActionController;
use App\Http\Controllers\Admin\Assets\AssetCategoryController;
use App\Http\Controllers\Admin\Assets\AssetDashboardController;
use App\Http\Controllers\Admin\Assets\AssetExportController;
use App\Http\Controllers\Admin\Assets\AssetLifecycleController;
use App\Http\Controllers\Admin\Assets\FixedAssetController;
use App\Http\Controllers\Admin\Assets\MachineController;
use App\Http\Controllers\Admin\Assets\MachineDashboardController;
use App\Http\Controllers\Admin\Assets\MaintenanceCalendarController;
use App\Http\Controllers\Admin\Assets\MaintenanceDashboardController;
use App\Http\Controllers\Admin\Assets\MaintenanceDowntimeController;
use App\Http\Controllers\Admin\Assets\MaintenancePlanController;
use App\Http\Controllers\Admin\Assets\MaintenanceTechnicianController;
use App\Http\Controllers\Admin\Assets\MaintenanceWorkOrderController;
use App\Http\Controllers\Admin\Assets\AssetBranchTransferController;
use App\Http\Controllers\Admin\Assets\AssetCustodyAssignmentController;
use App\Http\Controllers\Admin\Assets\AssetCustodyDashboardController;
use App\Http\Controllers\Admin\Assets\AssetHandoverController;
use App\Http\Controllers\Admin\Assets\AssetReturnController;
use App\Http\Controllers\Admin\Assets\AssetFinanceDashboardController;
use App\Http\Controllers\Admin\Assets\AssetFinanceReportController;
use App\Http\Controllers\Admin\Assets\AssetReconciliationController;
use App\Http\Controllers\Admin\Assets\AssetWriteOffController;
use App\Http\Controllers\Admin\Assets\DepreciationEntryController;
use App\Http\Controllers\Admin\Assets\DepreciationRunController;
use App\Http\Controllers\Admin\Assets\FixedAssetFinancialController;
use App\Http\Controllers\Admin\Assets\AssetAcquisitionDashboardController;
use App\Http\Controllers\Admin\Assets\AssetCapitalizationController;
use App\Http\Controllers\Admin\Assets\AssetCapitalizationReconciliationController;
use App\Http\Controllers\Admin\Assets\AssetWarrantyController;
use App\Http\Controllers\Admin\Assets\Asset360Controller;
use App\Http\Controllers\Admin\Assets\AssetExecutiveDashboardController;
use App\Http\Controllers\Admin\Assets\AssetBranchIntelligenceController;
use App\Http\Controllers\Admin\Assets\AssetAnalyticsController;
use App\Http\Controllers\Admin\Assets\AssetDocumentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/assets')
    ->name('admin.assets.')
    ->group(function () {
        Route::get('/', AssetDashboardController::class)
            ->middleware('permission:assets.view')
            ->name('dashboard');

        Route::middleware('permission:assets.create')->group(function () {
            Route::get('register/create', [FixedAssetController::class, 'create'])->name('create');
            Route::post('register', [FixedAssetController::class, 'store'])->name('store');
        });

        Route::middleware('permission:assets.view')->group(function () {
            Route::get('register', [FixedAssetController::class, 'index'])->name('index');
            Route::get('register/export/{format}', AssetExportController::class)
                ->where('format', 'csv|excel|pdf')
                ->name('export');
            Route::get('register/{asset}', [FixedAssetController::class, 'show'])
                ->whereNumber('asset')
                ->name('show');
            Route::get('register/{asset}/360', [Asset360Controller::class, 'show'])
                ->middleware('permission:assets.360.view')
                ->name('360.show');
            Route::get('register/{asset}/barcode', [AssetLifecycleController::class, 'barcode'])->name('barcode');
            Route::get('register/{asset}/documents', [AssetDocumentController::class, 'index'])->name('documents.index');
            Route::post('register/{asset}/documents', [AssetDocumentController::class, 'store'])->name('documents.store');
            Route::get('documents/{document}/download', [AssetDocumentController::class, 'download'])->name('documents.download');
            Route::post('documents/{document}/archive', [AssetDocumentController::class, 'archive'])->name('documents.archive');

            Route::get('categories', [AssetCategoryController::class, 'index'])->name('categories.index');
        });

        Route::middleware('permission:assets.edit')->group(function () {
            Route::get('register/{asset}/edit', [FixedAssetController::class, 'edit'])->name('edit');
            Route::put('register/{asset}', [FixedAssetController::class, 'update'])->name('update');
        });

        Route::middleware('permission:assets.categories.manage')->group(function () {
            Route::get('categories/create', [AssetCategoryController::class, 'create'])->name('categories.create');
            Route::post('categories', [AssetCategoryController::class, 'store'])->name('categories.store');
            Route::get('categories/{category}/edit', [AssetCategoryController::class, 'edit'])->name('categories.edit');
            Route::put('categories/{category}', [AssetCategoryController::class, 'update'])->name('categories.update');
            Route::post('categories/{category}/archive', [AssetCategoryController::class, 'archive'])->name('categories.archive');
        });

        Route::middleware('permission:machines.view')->group(function () {
            Route::get('machines', [MachineController::class, 'index'])->name('machines.index');
            Route::get('machines/dashboard', MachineDashboardController::class)->name('machines.dashboard');
            Route::get('machines/{asset}', [MachineController::class, 'show'])->name('machines.show');
        });

        Route::middleware('permission:machines.manage')->group(function () {
            Route::post('machines/{asset}/activate', [MachineController::class, 'activate'])->name('machines.activate');
            Route::post('machines/{asset}/status', [MachineController::class, 'updateStatus'])->name('machines.status');
        });

        Route::middleware('permission:machines.capacity.manage')->group(function () {
            Route::post('machines/{asset}/capacity', [MachineController::class, 'updateCapacity'])->name('machines.capacity');
        });

        Route::middleware('permission:machines.assign')->group(function () {
            Route::post('machines/{asset}/work-center', [MachineController::class, 'assignWorkCenter'])->name('machines.work-center');
        });

        Route::prefix('maintenance')->name('maintenance.')->group(function () {
            Route::middleware('permission:maintenance.view')->group(function () {
                Route::get('/', MaintenanceDashboardController::class)->name('dashboard');
                Route::get('work-orders', [MaintenanceWorkOrderController::class, 'index'])->name('work-orders.index');
                Route::get('work-orders/{workOrder}', [MaintenanceWorkOrderController::class, 'show'])->name('work-orders.show');
                Route::get('plans', [MaintenancePlanController::class, 'index'])->name('plans.index');
                Route::get('downtime', [MaintenanceDowntimeController::class, 'index'])->name('downtime.index');
                Route::get('technicians', [MaintenanceTechnicianController::class, 'index'])->name('technicians.index');
            });

            Route::middleware('permission:maintenance.calendar.view')->group(function () {
                Route::get('calendar', MaintenanceCalendarController::class)->name('calendar');
            });

            Route::middleware('permission:maintenance.create')->group(function () {
                Route::get('work-orders/create', [MaintenanceWorkOrderController::class, 'create'])->name('work-orders.create');
                Route::post('work-orders', [MaintenanceWorkOrderController::class, 'store'])->name('work-orders.store');
                Route::get('plans/create', [MaintenancePlanController::class, 'create'])->name('plans.create');
                Route::post('plans', [MaintenancePlanController::class, 'store'])->name('plans.store');
            });

            Route::middleware('permission:maintenance.manage')->group(function () {
                Route::post('work-orders/{workOrder}/open', [MaintenanceWorkOrderController::class, 'open'])->name('work-orders.open');
                Route::post('work-orders/{workOrder}/start', [MaintenanceWorkOrderController::class, 'start'])->name('work-orders.start');
                Route::post('work-orders/{workOrder}/status', [MaintenanceWorkOrderController::class, 'updateStatus'])->name('work-orders.status');
                Route::post('downtime', [MaintenanceDowntimeController::class, 'store'])->name('downtime.store');
                Route::post('technicians', [MaintenanceTechnicianController::class, 'store'])->name('technicians.store');
            });

            Route::middleware('permission:maintenance.assign')->group(function () {
                Route::post('work-orders/{workOrder}/assign', [MaintenanceWorkOrderController::class, 'assign'])->name('work-orders.assign');
            });

            Route::middleware('permission:maintenance.complete')->group(function () {
                Route::post('work-orders/{workOrder}/complete', [MaintenanceWorkOrderController::class, 'complete'])->name('work-orders.complete');
            });

            Route::middleware('permission:maintenance.close')->group(function () {
                Route::post('work-orders/{workOrder}/close', [MaintenanceWorkOrderController::class, 'close'])->name('work-orders.close');
            });
        });

        Route::prefix('custody')->name('custody.')->group(function () {
            Route::middleware('permission:assets.custody.view')->group(function () {
                Route::get('/', AssetCustodyDashboardController::class)->name('dashboard');
                Route::get('assignments', [AssetCustodyAssignmentController::class, 'index'])->name('assignments.index');
                Route::get('handovers', [AssetHandoverController::class, 'index'])->name('handovers.index');
                Route::get('handovers/{handover}', [AssetHandoverController::class, 'show'])->name('handovers.show');
                Route::get('returns', [AssetReturnController::class, 'index'])->name('returns.index');
                Route::get('transfers', [AssetBranchTransferController::class, 'index'])->name('transfers.index');
                Route::get('transfers/{transfer}', [AssetBranchTransferController::class, 'show'])->name('transfers.show');
            });

            Route::middleware('permission:assets.assign')->group(function () {
                Route::post('assignments', [AssetCustodyAssignmentController::class, 'store'])->name('assignments.store');
            });

            Route::middleware('permission:assets.handover.manage')->group(function () {
                Route::get('handovers/create', [AssetHandoverController::class, 'create'])->name('handovers.create');
                Route::post('handovers', [AssetHandoverController::class, 'store'])->name('handovers.store');
                Route::post('handovers/{handover}/submit', [AssetHandoverController::class, 'submit'])->name('handovers.submit');
                Route::post('handovers/{handover}/accept', [AssetHandoverController::class, 'accept'])->name('handovers.accept');
                Route::post('handovers/{handover}/reject', [AssetHandoverController::class, 'reject'])->name('handovers.reject');
            });

            Route::middleware('permission:assets.return')->group(function () {
                Route::get('returns/create', [AssetReturnController::class, 'create'])->name('returns.create');
                Route::post('returns', [AssetReturnController::class, 'store'])->name('returns.store');
            });

            Route::middleware('permission:assets.transfer')->group(function () {
                Route::get('transfers/create', [AssetBranchTransferController::class, 'create'])->name('transfers.create');
                Route::post('transfers', [AssetBranchTransferController::class, 'store'])->name('transfers.store');
                Route::post('transfers/{transfer}/accept', [AssetBranchTransferController::class, 'accept'])->name('transfers.accept');
                Route::post('transfers/{transfer}/reject', [AssetBranchTransferController::class, 'reject'])->name('transfers.reject');
            });

            Route::middleware('permission:assets.custody.manage')->group(function () {
                Route::post('transfers/{transfer}/approve', [AssetBranchTransferController::class, 'approve'])->name('transfers.approve');
            });
        });

        Route::prefix('intelligence')->name('intelligence.')->group(function () {
            Route::middleware('permission:assets.analytics.view')->group(function () {
                Route::get('executive', AssetExecutiveDashboardController::class)->name('executive');
                Route::get('branch', AssetBranchIntelligenceController::class)->name('branch');
                Route::get('analytics', AssetAnalyticsController::class)->name('analytics');
            });
        });

        Route::prefix('acquisitions')->name('acquisitions.')->group(function () {
            Route::middleware('permission:assets.acquisition.view')->group(function () {
                Route::get('/', AssetAcquisitionDashboardController::class)->name('dashboard');
                Route::get('queue', [AssetCapitalizationController::class, 'index'])->name('queue');
                Route::get('warranties', [AssetWarrantyController::class, 'index'])->name('warranties');
                Route::get('reconciliation', [AssetCapitalizationReconciliationController::class, 'index'])->name('reconciliation.index');
                Route::get('reconciliation/{reconciliation}', [AssetCapitalizationReconciliationController::class, 'show'])->name('reconciliation.show');
            });

            Route::middleware('permission:assets.capitalize.approve')->group(function () {
                Route::post('queue/{candidate}/approve', [AssetCapitalizationController::class, 'approve'])->name('approve');
            });

            Route::middleware('permission:assets.capitalize')->group(function () {
                Route::get('queue/{candidate}/workbench', [AssetCapitalizationController::class, 'workbench'])->name('workbench');
                Route::post('queue/{candidate}/capitalize', [AssetCapitalizationController::class, 'capitalize'])->name('capitalize');
                Route::post('queue/{candidate}/reject', [AssetCapitalizationController::class, 'reject'])->name('reject');
            });

            Route::middleware('permission:assets.warranty.manage')->group(function () {
                Route::post('register/{asset}/warranties', [AssetWarrantyController::class, 'store'])->name('warranties.store');
                Route::put('warranties/{warranty}', [AssetWarrantyController::class, 'update'])->name('warranties.update');
            });

            Route::middleware('permission:assets.reconciliation.view')->group(function () {
                Route::post('reconciliation', [AssetCapitalizationReconciliationController::class, 'store'])->name('reconciliation.store');
            });
        });

        Route::prefix('finance')->name('finance.')->group(function () {
            Route::middleware('permission:assets.depreciation.view')->group(function () {
                Route::get('/', AssetFinanceDashboardController::class)->name('dashboard');
                Route::get('runs', [DepreciationRunController::class, 'index'])->name('runs.index');
                Route::get('runs/{run}', [DepreciationRunController::class, 'show'])->name('runs.show');
                Route::get('entries', [DepreciationEntryController::class, 'index'])->name('entries.index');
                Route::get('reconciliation', [AssetReconciliationController::class, 'index'])->name('reconciliation.index');
                Route::get('reconciliation/{reconciliation}', [AssetReconciliationController::class, 'show'])->name('reconciliation.show');
                Route::get('reports', [AssetFinanceReportController::class, 'index'])->name('reports.index');
                Route::get('write-offs', [AssetWriteOffController::class, 'index'])->name('write-offs.index');
                Route::get('register/{asset}/financial', [FixedAssetFinancialController::class, 'show'])->name('profile');
            });

            Route::middleware('permission:assets.depreciation.run')->group(function () {
                Route::get('runs/create', [DepreciationRunController::class, 'create'])->name('runs.create');
                Route::post('runs', [DepreciationRunController::class, 'store'])->name('runs.store');
                Route::post('runs/{run}/preview', [DepreciationRunController::class, 'preview'])->name('runs.preview');
                Route::post('runs/{run}/cancel', [DepreciationRunController::class, 'cancel'])->name('runs.cancel');
            });

            Route::middleware('permission:assets.depreciation.post')->group(function () {
                Route::post('runs/{run}/execute', [DepreciationRunController::class, 'execute'])->name('runs.execute');
            });

            Route::middleware('permission:assets.reconciliation.view')->group(function () {
                Route::post('reconciliation', [AssetReconciliationController::class, 'store'])->name('reconciliation.store');
            });

            Route::middleware('permission:assets.writeoff.manage')->group(function () {
                Route::get('write-offs/create', [AssetWriteOffController::class, 'create'])->name('write-offs.create');
                Route::post('write-offs', [AssetWriteOffController::class, 'store'])->name('write-offs.store');
                Route::post('write-offs/{writeOff}/approve', [AssetWriteOffController::class, 'approve'])->name('write-offs.approve');
                Route::post('write-offs/{writeOff}/post', [AssetWriteOffController::class, 'post'])->name('write-offs.post');
            });
        });

        Route::middleware('permission:assets.manage')->group(function () {
            Route::post('register/bulk', AssetBulkActionController::class)->name('bulk');
            Route::get('register/{asset}/transfer', [AssetLifecycleController::class, 'transferForm'])->name('transfer');
            Route::post('register/{asset}/transfer', [AssetLifecycleController::class, 'transfer'])->name('transfer.store');
            Route::post('register/{asset}/assign', [FixedAssetController::class, 'assign'])->name('assign');
            Route::post('register/{asset}/status', [FixedAssetController::class, 'changeStatus'])->name('status');
            Route::post('register/{asset}/archive', [FixedAssetController::class, 'archive'])->name('archive');
            Route::post('register/{asset}/maintenance', [AssetLifecycleController::class, 'maintenance'])->name('maintenance');
            Route::post('register/{asset}/repair', [AssetLifecycleController::class, 'repair'])->name('repair');
            Route::post('register/{asset}/repair-complete', [AssetLifecycleController::class, 'repairComplete'])->name('repair-complete');
            Route::get('register/{asset}/dispose', [AssetLifecycleController::class, 'disposeForm'])->name('dispose');
            Route::post('register/{asset}/dispose', [AssetLifecycleController::class, 'dispose'])->name('dispose.store');
        });

        Route::middleware('permission:assets.depreciation.post')->group(function () {
            Route::post('register/{asset}/depreciate', [AssetLifecycleController::class, 'depreciate'])->name('depreciate');
        });
    });

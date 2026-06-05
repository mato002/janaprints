<?php

use App\Http\Controllers\Admin\Reports\IntelligenceReportController;
use App\Http\Controllers\Admin\Reports\ProductionReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant', \App\Http\Middleware\CaptureWorkspaceNavigationQuery::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::middleware('permission:reports.view')->prefix('reports')->name('reports.')->group(function () {
            Route::get('executive', [IntelligenceReportController::class, 'executive'])->name('executive');
            Route::get('commercial', [IntelligenceReportController::class, 'commercial'])->name('commercial');
            Route::get('production', [ProductionReportController::class, 'index'])->name('production');
            Route::get('production/print', [ProductionReportController::class, 'print'])->name('production.print');
            Route::post('production/export', [ProductionReportController::class, 'export'])->name('production.export');
            Route::get('procurement', [IntelligenceReportController::class, 'procurement'])->name('procurement');
            Route::get('accounting', [IntelligenceReportController::class, 'accounting'])->name('accounting');
            Route::get('hr', [IntelligenceReportController::class, 'hr'])->name('hr');

            Route::middleware('permission:intelligence.inventory.view|reports.view')
                ->get('inventory-360', [IntelligenceReportController::class, 'inventory360'])
                ->name('inventory360');

            Route::middleware('permission:intelligence.vendor.view|reports.view')
                ->get('procurement-360', [IntelligenceReportController::class, 'procurement360'])
                ->name('procurement360');

            Route::middleware('permission:intelligence.branch.view|reports.view')
                ->get('branch-360', [IntelligenceReportController::class, 'branch360'])
                ->name('branch360');

            Route::middleware('permission:intelligence.production.view|reports.view')
                ->get('production-360', [IntelligenceReportController::class, 'production360'])
                ->name('production360');

            Route::middleware('permission:intelligence.financial.view|reports.view')
                ->get('financial-360', [IntelligenceReportController::class, 'financial360'])
                ->name('financial360');

            Route::middleware('permission:intelligence.commercial.view|reports.view')
                ->get('commercial-360', [IntelligenceReportController::class, 'commercial360'])
                ->name('commercial360');

            Route::middleware('permission:intelligence.assets.view|assets.analytics.view|reports.view')
                ->get('asset-360', [IntelligenceReportController::class, 'asset360'])
                ->name('asset360');
        });

        Route::middleware('permission:reports.view|reports.inventory.view')
            ->get('reports/inventory', [IntelligenceReportController::class, 'inventory'])
            ->name('reports.inventory');

        Route::middleware('permission:kpi.view|reports.view')
            ->get('reports/kpi', [IntelligenceReportController::class, 'kpi'])
            ->name('reports.kpi');
    });

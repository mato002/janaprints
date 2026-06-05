<?php

use App\Http\Controllers\Admin\Commercial\CommercialActivityController;
use App\Http\Controllers\Admin\Commercial\CommercialArtworkReportController;
use App\Http\Controllers\Admin\Commercial\CommercialConversionReportController;
use App\Http\Controllers\Admin\Commercial\CommercialCustomerReportController;
use App\Http\Controllers\Admin\Commercial\CommercialQuotationReportController;
use App\Http\Controllers\Admin\Commercial\CommercialReportExportController;
use App\Http\Controllers\Admin\Commercial\CommercialSalesOrderReportController;
use App\Http\Controllers\Admin\Commercial\CommercialSalesReportController;
use App\Http\Controllers\Admin\Commercial\CommercialPosReportController;
use App\Http\Controllers\Admin\Commercial\PosCertificationController;
use App\Http\Controllers\Admin\Commercial\PosCashReconciliationController;
use App\Http\Controllers\Admin\Commercial\PosReturnController;
use App\Http\Controllers\Admin\Commercial\PosCounterSalesController;
use App\Http\Controllers\Admin\Commercial\PosSaleController;
use App\Http\Controllers\Admin\Commercial\PosSessionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/commercial')
    ->name('admin.commercial.')
    ->group(function () {
        Route::middleware('permission:crm.activities.view')->group(function () {
            Route::get('activities', [CommercialActivityController::class, 'index'])->name('activities.index');
            Route::get('activities/{activity}', [CommercialActivityController::class, 'show'])->name('activities.show');
        });

        Route::middleware('permission:crm.activities.create')->group(function () {
            Route::get('activities/create', [CommercialActivityController::class, 'create'])->name('activities.create');
            Route::post('activities', [CommercialActivityController::class, 'store'])->name('activities.store');
        });

        Route::middleware('permission:crm.activities.edit')->group(function () {
            Route::get('activities/{activity}/edit', [CommercialActivityController::class, 'edit'])->name('activities.edit');
            Route::put('activities/{activity}', [CommercialActivityController::class, 'update'])->name('activities.update');
        });

        Route::middleware('permission:crm.activities.delete')->group(function () {
            Route::delete('activities/{activity}', [CommercialActivityController::class, 'destroy'])->name('activities.destroy');
        });

        Route::middleware('permission:pos.view|pos.counter_sales.view')->group(function () {
            Route::get('pos', [PosSaleController::class, 'dashboard'])->name('pos.dashboard');
            Route::get('pos/sales', [PosSaleController::class, 'index'])->name('pos.index');
            Route::get('pos/holds', [PosSaleController::class, 'holds'])->name('pos.holds');
            Route::get('pos/sales/{sale}', [PosSaleController::class, 'show'])->name('pos.show');
            Route::get('pos/sales/{sale}/receipt', [PosSaleController::class, 'receipt'])->name('pos.receipt');
            Route::get('pos/counter-sales', [PosCounterSalesController::class, 'index'])->name('pos.counter-sales');
            Route::get('pos/counter-sales/products/search', [PosCounterSalesController::class, 'searchProducts'])->name('pos.counter-sales.products.search');
        });

        Route::middleware('permission:pos.create|pos.counter_sales.create')->group(function () {
            Route::get('pos/new', [PosSaleController::class, 'create'])->name('pos.create');
            Route::post('pos/sales', [PosSaleController::class, 'store'])->name('pos.store');
        });

        Route::middleware('permission:pos.edit|pos.counter_sales.complete|pos.counter_sales.hold')->group(function () {
            Route::get('pos/sales/{sale}/resume', [PosSaleController::class, 'resume'])->name('pos.resume');
            Route::post('pos/sales/{sale}/pay', [PosSaleController::class, 'pay'])->name('pos.pay');
        });

        Route::middleware('permission:pos.cancel|pos.counter_sales.cancel')->group(function () {
            Route::post('pos/sales/{sale}/cancel', [PosSaleController::class, 'cancel'])->name('pos.cancel');
        });

        Route::middleware('permission:pos.refund')->group(function () {
            Route::post('pos/sales/{sale}/refund', [PosSaleController::class, 'refund'])->name('pos.refund');
        });

        Route::middleware('permission:commercial.pos.returns.view')->group(function () {
            Route::get('pos/returns', [PosReturnController::class, 'dashboard'])->name('pos.returns.dashboard');
            Route::get('pos/returns/history', [PosReturnController::class, 'index'])->name('pos.returns.index');
        });

        Route::middleware('permission:commercial.pos.returns.create')->group(function () {
            Route::get('pos/returns/create', [PosReturnController::class, 'create'])->name('pos.returns.create');
            Route::post('pos/returns', [PosReturnController::class, 'store'])->name('pos.returns.store');
        });

        Route::middleware('permission:commercial.pos.returns.view')->group(function () {
            Route::get('pos/returns/{return}', [PosReturnController::class, 'show'])->name('pos.returns.show');
        });

        Route::middleware('permission:commercial.pos.returns.approve')->group(function () {
            Route::post('pos/returns/{return}/approve', [PosReturnController::class, 'approve'])->name('pos.returns.approve');
            Route::post('pos/returns/{return}/reject', [PosReturnController::class, 'reject'])->name('pos.returns.reject');
        });

        Route::middleware('permission:commercial.pos.sessions.open')->group(function () {
            Route::get('pos/sessions/open', [PosSessionController::class, 'create'])->name('pos.sessions.create');
            Route::post('pos/sessions', [PosSessionController::class, 'store'])->name('pos.sessions.store');
        });

        Route::middleware('permission:commercial.pos.sessions.view')->group(function () {
            Route::get('pos/sessions', [PosSessionController::class, 'index'])->name('pos.sessions.index');
        });

        Route::middleware('permission:commercial.pos.sessions.close')->group(function () {
            Route::get('pos/sessions/{session}/close', [PosSessionController::class, 'closeForm'])->name('pos.sessions.close');
            Route::post('pos/sessions/{session}/close', [PosSessionController::class, 'close'])->name('pos.sessions.close.store');
        });

        Route::middleware('permission:commercial.pos.sessions.view')->group(function () {
            Route::get('pos/sessions/{session}', [PosSessionController::class, 'show'])->name('pos.sessions.show');
        });

        Route::middleware('permission:commercial.pos.reconciliation.view')->group(function () {
            Route::get('pos/reconciliation', [PosCashReconciliationController::class, 'index'])->name('pos.reconciliation.index');
            Route::get('pos/reconciliation/history', [PosCashReconciliationController::class, 'history'])->name('pos.reconciliation.history');
            Route::get('pos/reconciliation/{reconciliation}', [PosCashReconciliationController::class, 'show'])->name('pos.reconciliation.show');
        });

        Route::middleware('permission:commercial.pos.reconciliation.create')->group(function () {
            Route::post('pos/reconciliation/{reconciliation}/submit', [PosCashReconciliationController::class, 'submit'])->name('pos.reconciliation.submit');
        });

        Route::middleware('permission:commercial.pos.reconciliation.approve')->group(function () {
            Route::post('pos/reconciliation/{reconciliation}/review', [PosCashReconciliationController::class, 'review'])->name('pos.reconciliation.review');
            Route::post('pos/reconciliation/{reconciliation}/approve', [PosCashReconciliationController::class, 'approve'])->name('pos.reconciliation.approve');
            Route::post('pos/reconciliation/{reconciliation}/reject', [PosCashReconciliationController::class, 'reject'])->name('pos.reconciliation.reject');
        });

    });

Route::middleware(['auth', 'verified', 'tenant', \App\Http\Middleware\CaptureWorkspaceNavigationQuery::class])
    ->prefix('admin/commercial/pos/certification')
    ->group(function () {
        Route::get('/', [PosCertificationController::class, 'index'])
            ->middleware('permission:commercial.pos.certification.view')
            ->name('admin.commercial.pos.certification.index');
    });

Route::middleware(['auth', 'verified', 'tenant', \App\Http\Middleware\CaptureWorkspaceNavigationQuery::class])
    ->prefix('admin/commercial/pos/intelligence')
    ->group(function () {
        Route::get('/', [CommercialPosReportController::class, 'index'])
            ->middleware('permission:commercial.pos.reports.view')
            ->name('commercial.pos.reports.index');

        Route::post('export', [CommercialPosReportController::class, 'export'])
            ->middleware('permission:commercial.pos.reports.export')
            ->name('commercial.pos.reports.export');
    });

Route::middleware(['auth', 'verified', 'tenant', \App\Http\Middleware\CaptureWorkspaceNavigationQuery::class])
    ->prefix('admin/commercial/reports')
    ->group(function () {
        Route::get('sales', [CommercialSalesReportController::class, 'index'])
            ->middleware('permission:commercial.reports.sales.view')
            ->name('commercial.reports.sales.index');

        Route::post('sales/export', [CommercialSalesReportController::class, 'export'])
            ->middleware('permission:commercial.reports.export')
            ->name('commercial.reports.sales.export');

        Route::get('quotations', [CommercialQuotationReportController::class, 'index'])
            ->middleware('permission:commercial.reports.quotations.view')
            ->name('commercial.reports.quotations.index');

        Route::post('quotations/export', [CommercialQuotationReportController::class, 'export'])
            ->middleware('permission:commercial.reports.export')
            ->name('commercial.reports.quotations.export');

        Route::get('sales-orders', [CommercialSalesOrderReportController::class, 'index'])
            ->middleware('permission:commercial.reports.sales_orders.view')
            ->name('commercial.reports.sales_orders.index');

        Route::post('sales-orders/export', [CommercialSalesOrderReportController::class, 'export'])
            ->middleware('permission:commercial.reports.export')
            ->name('commercial.reports.sales_orders.export');

        Route::get('customers', [CommercialCustomerReportController::class, 'index'])
            ->middleware('permission:commercial.reports.customers.view')
            ->name('commercial.reports.customers.index');

        Route::post('customers/export', [CommercialCustomerReportController::class, 'export'])
            ->middleware('permission:commercial.reports.export')
            ->name('commercial.reports.customers.export');

        Route::get('artwork', [CommercialArtworkReportController::class, 'index'])
            ->middleware('permission:commercial.reports.artwork.view')
            ->name('commercial.reports.artwork.index');

        Route::post('artwork/export', [CommercialArtworkReportController::class, 'export'])
            ->middleware('permission:commercial.reports.export')
            ->name('commercial.reports.artwork.export');

        Route::get('conversion', [CommercialConversionReportController::class, 'index'])
            ->middleware('permission:commercial.reports.conversion.view')
            ->name('commercial.reports.conversion.index');

        Route::post('conversion/export', [CommercialConversionReportController::class, 'export'])
            ->middleware('permission:commercial.reports.export')
            ->name('commercial.reports.conversion.export');

        Route::get('exports', [CommercialReportExportController::class, 'index'])
            ->middleware('permission:commercial.reports.exports.view')
            ->name('commercial.reports.exports.index');

        Route::get('exports/{export}/download', [CommercialReportExportController::class, 'download'])
            ->middleware('permission:commercial.reports.exports.download')
            ->name('commercial.reports.exports.download');

        Route::get('exports/{export}/status', [CommercialReportExportController::class, 'status'])
            ->middleware('permission:commercial.reports.exports.view')
            ->name('commercial.reports.exports.status');
    });

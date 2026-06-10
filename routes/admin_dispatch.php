<?php

use App\Http\Controllers\Admin\Dispatch\DispatchInventoryReportController;
use App\Http\Controllers\Admin\Dispatch\DeliveryCalendarController;
use App\Http\Controllers\Admin\Dispatch\DeliveryNoteController;
use App\Http\Controllers\Admin\Dispatch\DispatchDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/dispatch')
    ->name('admin.dispatch.')
    ->group(function () {
        Route::get('/', DispatchDashboardController::class)
            ->middleware('permission:dispatch.view')
            ->name('dashboard');

        Route::middleware('permission:dispatch.view')->group(function () {
            Route::get('calendar', DeliveryCalendarController::class)->name('calendar');
            Route::get('delivery-notes', [DeliveryNoteController::class, 'index'])->name('delivery-notes.index');
            Route::get('delivery-notes/{deliveryNote}', [DeliveryNoteController::class, 'show'])->name('delivery-notes.show');
            Route::get('reports/transit-inventory', [DispatchInventoryReportController::class, 'transit'])->name('reports.transit-inventory');
            Route::get('reports/cogs-postings', [DispatchInventoryReportController::class, 'cogs'])->name('reports.cogs-postings');
        });

        Route::middleware('permission:dispatch.create')->group(function () {
            Route::post('job-cards/{jobCard}/delivery-notes', [DeliveryNoteController::class, 'storeFromJob'])
                ->name('delivery-notes.store-from-job');
        });

        Route::middleware('permission:dispatch.dispatch')->group(function () {
            Route::post('delivery-notes/{deliveryNote}/dispatch', [DeliveryNoteController::class, 'dispatch'])
                ->name('delivery-notes.dispatch');
        });

        Route::middleware('permission:dispatch.deliver')->group(function () {
            Route::post('delivery-notes/{deliveryNote}/deliver', [DeliveryNoteController::class, 'deliver'])
                ->name('delivery-notes.deliver');
        });

        Route::middleware('permission:invoices.create')->group(function () {
            Route::post('delivery-notes/{deliveryNote}/generate-invoice', [DeliveryNoteController::class, 'generateInvoice'])
                ->name('delivery-notes.generate-invoice');
        });

        Route::middleware('permission:dispatch.cancel')->group(function () {
            Route::post('delivery-notes/{deliveryNote}/cancel', [DeliveryNoteController::class, 'cancel'])
                ->name('delivery-notes.cancel');
        });
    });

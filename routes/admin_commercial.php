<?php

use App\Http\Controllers\Admin\Commercial\CommercialActivityController;
use App\Http\Controllers\Admin\Commercial\PosSaleController;
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

        Route::middleware('permission:pos.view')->group(function () {
            Route::get('pos', [PosSaleController::class, 'dashboard'])->name('pos.dashboard');
            Route::get('pos/sales', [PosSaleController::class, 'index'])->name('pos.index');
            Route::get('pos/holds', [PosSaleController::class, 'holds'])->name('pos.holds');
            Route::get('pos/sales/{sale}', [PosSaleController::class, 'show'])->name('pos.show');
            Route::get('pos/sales/{sale}/receipt', [PosSaleController::class, 'receipt'])->name('pos.receipt');
        });

        Route::middleware('permission:pos.create')->group(function () {
            Route::get('pos/new', [PosSaleController::class, 'create'])->name('pos.create');
            Route::post('pos/sales', [PosSaleController::class, 'store'])->name('pos.store');
        });

        Route::middleware('permission:pos.edit')->group(function () {
            Route::post('pos/sales/{sale}/resume', [PosSaleController::class, 'resume'])->name('pos.resume');
        });

        Route::middleware('permission:pos.cancel')->group(function () {
            Route::post('pos/sales/{sale}/cancel', [PosSaleController::class, 'cancel'])->name('pos.cancel');
        });

        Route::middleware('permission:pos.refund')->group(function () {
            Route::post('pos/sales/{sale}/refund', [PosSaleController::class, 'refund'])->name('pos.refund');
        });
    });

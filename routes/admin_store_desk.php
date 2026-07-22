<?php

use App\Http\Controllers\Admin\Inventory\StoreDeskController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/store')
    ->name('admin.store.')
    ->group(function () {
        Route::get('desk', [StoreDeskController::class, 'index'])
            ->middleware('permission:inventory.view')
            ->name('desk');

        Route::get('desk/catalogue', [StoreDeskController::class, 'catalogue'])
            ->middleware('permission:inventory.view')
            ->name('desk.catalogue');

        Route::get('desk/reorder-alerts', [StoreDeskController::class, 'reorderAlerts'])
            ->middleware('permission:inventory.reorder.view')
            ->name('desk.reorder-alerts');
    });

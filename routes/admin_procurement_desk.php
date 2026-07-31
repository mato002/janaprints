<?php

use App\Http\Controllers\Admin\Procurement\BuyDeskController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/procurement')
    ->name('admin.procurement.')
    ->group(function () {
        Route::get('desk', [BuyDeskController::class, 'index'])
            ->middleware('permission:procurement.vendors.view')
            ->name('desk');
    });

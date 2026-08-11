<?php

use App\Http\Controllers\Admin\Sales\SalesDeskController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/sales/desk')
    ->name('admin.sales.')
    ->group(function () {
        Route::get('/', [SalesDeskController::class, 'index'])
            ->middleware('permission:crm.customers.create|sales_orders.create')
            ->name('desk');

        Route::middleware('permission:crm.customers.view')->group(function () {
            Route::get('customers/search', [SalesDeskController::class, 'searchCustomers'])->name('desk.customers.search');
        });

        Route::middleware('permission:sales_orders.view')->group(function () {
            Route::get('orders/{salesOrder}/materials', [SalesDeskController::class, 'materialsHandoff'])
                ->name('desk.materials');
            Route::post('orders/{salesOrder}/park', [SalesDeskController::class, 'parkWalkIn'])
                ->name('desk.park');
        });
    });

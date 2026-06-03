<?php

use App\Http\Controllers\Admin\Inventory\InventoryDashboardController;
use App\Http\Controllers\Admin\Inventory\InventoryItemController;
use App\Http\Controllers\Admin\Inventory\InventoryMovementController;
use App\Http\Controllers\Admin\Inventory\ProductionMaterialConsumptionController;
use App\Http\Controllers\Admin\Inventory\StockAdjustmentController;
use App\Http\Controllers\Admin\Inventory\StockIssueController;
use App\Http\Controllers\Admin\Inventory\StockReceiptController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/inventory')
    ->name('admin.inventory.')
    ->group(function () {
        Route::get('/', InventoryDashboardController::class)
            ->middleware('permission:inventory.view')
            ->name('dashboard');

        Route::middleware('permission:inventory.view')->group(function () {
            Route::get('items', [InventoryItemController::class, 'index'])->name('items.index');
            Route::get('movements', [InventoryMovementController::class, 'index'])->name('movements.index');
            Route::get('receipts', [StockReceiptController::class, 'index'])->name('receipts.index');
            Route::get('issues', [StockIssueController::class, 'index'])->name('issues.index');
            Route::get('adjustments', [StockAdjustmentController::class, 'index'])->name('adjustments.index');
        });

        Route::middleware('permission:inventory.create')->group(function () {
            Route::get('items/create', [InventoryItemController::class, 'create'])->name('items.create');
            Route::post('items', [InventoryItemController::class, 'store'])->name('items.store');
        });

        Route::middleware('permission:inventory.view')->group(function () {
            Route::get('items/{item}', [InventoryItemController::class, 'show'])->name('items.show');
        });

        Route::middleware('permission:inventory.edit')->group(function () {
            Route::get('items/{item}/edit', [InventoryItemController::class, 'edit'])->name('items.edit');
            Route::put('items/{item}', [InventoryItemController::class, 'update'])->name('items.update');
        });

        Route::middleware('permission:inventory.delete')->group(function () {
            Route::delete('items/{item}', [InventoryItemController::class, 'destroy'])->name('items.destroy');
        });

        Route::middleware('permission:inventory.view')->group(function () {
            Route::get('receipts/{receipt}', [StockReceiptController::class, 'show'])->name('receipts.show');
            Route::get('issues/{issue}', [StockIssueController::class, 'show'])->name('issues.show');
            Route::get('adjustments/{adjustment}', [StockAdjustmentController::class, 'show'])->name('adjustments.show');
        });

        Route::middleware('permission:inventory.receive')->group(function () {
            Route::get('receipts/create', [StockReceiptController::class, 'create'])->name('receipts.create');
            Route::post('receipts', [StockReceiptController::class, 'store'])->name('receipts.store');
            Route::post('receipts/{receipt}/post', [StockReceiptController::class, 'post'])->name('receipts.post');
        });

        Route::middleware('permission:inventory.issue')->group(function () {
            Route::get('issues/create', [StockIssueController::class, 'create'])->name('issues.create');
            Route::post('issues', [StockIssueController::class, 'store'])->name('issues.store');
            Route::post('issues/{issue}/post', [StockIssueController::class, 'post'])->name('issues.post');
        });

        Route::middleware('permission:inventory.adjust')->group(function () {
            Route::get('adjustments/create', [StockAdjustmentController::class, 'create'])->name('adjustments.create');
            Route::post('adjustments', [StockAdjustmentController::class, 'store'])->name('adjustments.store');
            Route::post('adjustments/{adjustment}/post', [StockAdjustmentController::class, 'post'])->name('adjustments.post');
        });

        Route::middleware('permission:inventory.issue')->group(function () {
            Route::post('production/job-cards/{jobCard}/consume', [ProductionMaterialConsumptionController::class, 'store'])
                ->name('production.consume');
        });
    });

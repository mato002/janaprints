<?php

use App\Http\Controllers\Admin\Inventory\BrandController;
use App\Http\Controllers\Admin\Inventory\CatalogueDashboardController;
use App\Http\Controllers\Admin\Inventory\InventoryDashboardController;
use App\Http\Controllers\Admin\Inventory\InventoryValuationController;
use App\Http\Controllers\Admin\Inventory\InventoryCategoryController;
use App\Http\Controllers\Admin\Inventory\InventoryItemImageController;
use App\Http\Controllers\Admin\Inventory\InventoryItemController;
use App\Http\Controllers\Admin\Inventory\InventoryMovementController;
use App\Http\Controllers\Admin\Inventory\InventorySubcategoryController;
use App\Http\Controllers\Admin\Inventory\ItemAttributeController;
use App\Http\Controllers\Admin\Inventory\PriceListController;
use App\Http\Controllers\Admin\Inventory\ProductionMaterialConsumptionController;
use App\Http\Controllers\Admin\Inventory\StockAdjustmentController;
use App\Http\Controllers\Admin\Inventory\StockIssueController;
use App\Http\Controllers\Admin\Inventory\StockReceiptController;
use App\Http\Controllers\Admin\Inventory\StoreBalanceController;
use App\Http\Controllers\Admin\Inventory\StoreDashboardController;
use App\Http\Controllers\Admin\Inventory\StorePermissionController;
use App\Http\Controllers\Admin\Inventory\StoreTransferController;
use App\Http\Controllers\Admin\Inventory\WarehouseController;
use App\Http\Controllers\Admin\Inventory\WarehouseManagerController;
use App\Http\Controllers\Admin\Inventory\StockCountController;
use App\Http\Controllers\Admin\Inventory\CycleCountController;
use App\Http\Controllers\Admin\Inventory\InventoryVarianceController;
use App\Http\Controllers\Admin\Inventory\InventoryReconciliationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/inventory')
    ->name('admin.inventory.')
    ->group(function () {
        Route::get('/', InventoryDashboardController::class)
            ->middleware('permission:inventory.view')
            ->name('dashboard');

        Route::middleware('permission:inventory.view')->group(function () {
            Route::get('store', StoreDashboardController::class)->name('store.dashboard');
            Route::get('store/balances', StoreBalanceController::class)->name('store.balances');
            Route::get('store/permissions', StorePermissionController::class)->name('store.permissions');
            Route::get('warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
            Route::get('warehouses/{warehouse}', [WarehouseController::class, 'show'])->whereNumber('warehouse')->name('warehouses.show');
            Route::get('warehouses/{warehouse}/balances', [WarehouseController::class, 'balances'])->whereNumber('warehouse')->name('warehouses.balances');
            Route::get('transfers', [StoreTransferController::class, 'index'])->name('transfers.index');
            Route::get('transfers/{transfer}', [StoreTransferController::class, 'show'])->whereNumber('transfer')->name('transfers.show');
            Route::get('movements', [InventoryMovementController::class, 'index'])->name('movements.index');
            Route::get('receipts', [StockReceiptController::class, 'index'])->name('receipts.index');
            Route::get('issues', [StockIssueController::class, 'index'])->name('issues.index');
            Route::get('adjustments', [StockAdjustmentController::class, 'index'])->name('adjustments.index');
        });

        Route::middleware('permission:inventory.valuation.view')->group(function () {
            Route::get('valuation', [InventoryValuationController::class, 'index'])->name('valuation.index');
            Route::post('valuation/snapshot', [InventoryValuationController::class, 'snapshot'])->name('valuation.snapshot');
        });

        Route::middleware('permission:catalogue.view')->group(function () {
            Route::get('catalogue', CatalogueDashboardController::class)->name('catalogue.dashboard');
            Route::get('catalogue/categories', [InventoryCategoryController::class, 'index'])->name('catalogue.categories.index');
            Route::get('catalogue/subcategories', [InventorySubcategoryController::class, 'index'])->name('catalogue.subcategories.index');
            Route::get('catalogue/brands', [BrandController::class, 'index'])->name('catalogue.brands.index');
            Route::get('catalogue/attributes', [ItemAttributeController::class, 'index'])->name('catalogue.attributes.index');
            Route::get('catalogue/price-lists', [PriceListController::class, 'index'])->name('catalogue.price-lists.index');
            Route::get('items', [InventoryItemController::class, 'index'])->name('items.index');
        });

        Route::middleware('permission:catalogue.create')->group(function () {
            Route::get('catalogue/categories/create', [InventoryCategoryController::class, 'create'])->name('catalogue.categories.create');
            Route::post('catalogue/categories', [InventoryCategoryController::class, 'store'])->name('catalogue.categories.store');
            Route::get('catalogue/subcategories/create', [InventorySubcategoryController::class, 'create'])->name('catalogue.subcategories.create');
            Route::post('catalogue/subcategories', [InventorySubcategoryController::class, 'store'])->name('catalogue.subcategories.store');
            Route::get('catalogue/brands/create', [BrandController::class, 'create'])->name('catalogue.brands.create');
            Route::post('catalogue/brands', [BrandController::class, 'store'])->name('catalogue.brands.store');
            Route::get('catalogue/attributes/create', [ItemAttributeController::class, 'create'])->name('catalogue.attributes.create');
            Route::post('catalogue/attributes', [ItemAttributeController::class, 'store'])->name('catalogue.attributes.store');
            Route::get('catalogue/price-lists/create', [PriceListController::class, 'create'])->name('catalogue.price-lists.create');
            Route::post('catalogue/price-lists', [PriceListController::class, 'store'])->name('catalogue.price-lists.store');
            Route::get('items/create', [InventoryItemController::class, 'create'])->name('items.create');
            Route::post('items', [InventoryItemController::class, 'store'])->name('items.store');
        });

        Route::middleware('permission:catalogue.view')->group(function () {
            Route::get('items/{item}', [InventoryItemController::class, 'show'])->whereNumber('item')->name('items.show');
        });

        Route::middleware('permission:catalogue.edit')->group(function () {
            Route::get('catalogue/categories/{category}/edit', [InventoryCategoryController::class, 'edit'])->whereNumber('category')->name('catalogue.categories.edit');
            Route::put('catalogue/categories/{category}', [InventoryCategoryController::class, 'update'])->whereNumber('category')->name('catalogue.categories.update');
            Route::get('catalogue/subcategories/{subcategory}/edit', [InventorySubcategoryController::class, 'edit'])->whereNumber('subcategory')->name('catalogue.subcategories.edit');
            Route::put('catalogue/subcategories/{subcategory}', [InventorySubcategoryController::class, 'update'])->whereNumber('subcategory')->name('catalogue.subcategories.update');
            Route::get('catalogue/brands/{brand}/edit', [BrandController::class, 'edit'])->whereNumber('brand')->name('catalogue.brands.edit');
            Route::put('catalogue/brands/{brand}', [BrandController::class, 'update'])->whereNumber('brand')->name('catalogue.brands.update');
            Route::get('catalogue/attributes/{attribute}/edit', [ItemAttributeController::class, 'edit'])->whereNumber('attribute')->name('catalogue.attributes.edit');
            Route::put('catalogue/attributes/{attribute}', [ItemAttributeController::class, 'update'])->whereNumber('attribute')->name('catalogue.attributes.update');
            Route::get('catalogue/price-lists/{priceList}/edit', [PriceListController::class, 'edit'])->whereNumber('priceList')->name('catalogue.price-lists.edit');
            Route::put('catalogue/price-lists/{priceList}', [PriceListController::class, 'update'])->whereNumber('priceList')->name('catalogue.price-lists.update');
            Route::post('items/{item}/images', [InventoryItemImageController::class, 'store'])->whereNumber('item')->name('items.images.store');
            Route::patch('items/{item}/images/{image}/primary', [InventoryItemImageController::class, 'primary'])->whereNumber(['item', 'image'])->name('items.images.primary');
            Route::delete('items/{item}/images/{image}', [InventoryItemImageController::class, 'destroy'])->whereNumber(['item', 'image'])->name('items.images.destroy');
            Route::get('items/{item}/edit', [InventoryItemController::class, 'edit'])->name('items.edit');
            Route::put('items/{item}', [InventoryItemController::class, 'update'])->name('items.update');
        });

        Route::middleware('permission:catalogue.delete')->group(function () {
            Route::delete('catalogue/categories/{category}', [InventoryCategoryController::class, 'destroy'])->whereNumber('category')->name('catalogue.categories.destroy');
            Route::delete('catalogue/subcategories/{subcategory}', [InventorySubcategoryController::class, 'destroy'])->whereNumber('subcategory')->name('catalogue.subcategories.destroy');
            Route::delete('catalogue/brands/{brand}', [BrandController::class, 'destroy'])->whereNumber('brand')->name('catalogue.brands.destroy');
            Route::delete('catalogue/attributes/{attribute}', [ItemAttributeController::class, 'destroy'])->whereNumber('attribute')->name('catalogue.attributes.destroy');
            Route::delete('catalogue/price-lists/{priceList}', [PriceListController::class, 'destroy'])->whereNumber('priceList')->name('catalogue.price-lists.destroy');
            Route::delete('items/{item}', [InventoryItemController::class, 'destroy'])->name('items.destroy');
        });

        Route::middleware('permission:inventory.create')->group(function () {
            Route::get('warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
            Route::post('warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
        });

        Route::middleware('permission:inventory.edit')->group(function () {
            Route::get('warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->whereNumber('warehouse')->name('warehouses.edit');
            Route::put('warehouses/{warehouse}', [WarehouseController::class, 'update'])->whereNumber('warehouse')->name('warehouses.update');
            Route::patch('warehouses/{warehouse}/deactivate', [WarehouseController::class, 'deactivate'])->whereNumber('warehouse')->name('warehouses.deactivate');
            Route::patch('warehouses/{warehouse}/reactivate', [WarehouseController::class, 'reactivate'])->whereNumber('warehouse')->name('warehouses.reactivate');
            Route::get('warehouses/{warehouse}/managers', [WarehouseManagerController::class, 'edit'])->whereNumber('warehouse')->name('warehouses.managers.edit');
            Route::put('warehouses/{warehouse}/managers', [WarehouseManagerController::class, 'update'])->whereNumber('warehouse')->name('warehouses.managers.update');
        });

        Route::middleware('permission:inventory.delete')->group(function () {
            Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->whereNumber('warehouse')->name('warehouses.destroy');
        });

        Route::middleware('permission:inventory.receive')->group(function () {
            Route::get('receipts/create', [StockReceiptController::class, 'create'])->name('receipts.create');
            Route::post('receipts', [StockReceiptController::class, 'store'])->name('receipts.store');
            Route::post('receipts/{receipt}/post', [StockReceiptController::class, 'post'])->whereNumber('receipt')->name('receipts.post');
        });

        Route::middleware('permission:inventory.issue')->group(function () {
            Route::get('issues/create', [StockIssueController::class, 'create'])->name('issues.create');
            Route::post('issues', [StockIssueController::class, 'store'])->name('issues.store');
            Route::post('issues/{issue}/post', [StockIssueController::class, 'post'])->whereNumber('issue')->name('issues.post');
        });

        Route::middleware('permission:inventory.transfer')->group(function () {
            Route::get('transfers/create', [StoreTransferController::class, 'create'])->name('transfers.create');
            Route::post('transfers', [StoreTransferController::class, 'store'])->name('transfers.store');
            Route::post('transfers/{transfer}/post', [StoreTransferController::class, 'post'])->whereNumber('transfer')->name('transfers.post');
        });

        Route::middleware('permission:inventory.adjust')->group(function () {
            Route::get('adjustments/create', [StockAdjustmentController::class, 'create'])->name('adjustments.create');
            Route::post('adjustments', [StockAdjustmentController::class, 'store'])->name('adjustments.store');
            Route::post('adjustments/{adjustment}/post', [StockAdjustmentController::class, 'post'])->whereNumber('adjustment')->name('adjustments.post');
        });

        Route::middleware('permission:inventory.view')->group(function () {
            Route::get('receipts/{receipt}', [StockReceiptController::class, 'show'])->whereNumber('receipt')->name('receipts.show');
            Route::get('issues/{issue}', [StockIssueController::class, 'show'])->whereNumber('issue')->name('issues.show');
            Route::get('adjustments/{adjustment}', [StockAdjustmentController::class, 'show'])->whereNumber('adjustment')->name('adjustments.show');
        });

        Route::middleware('permission:inventory.issue')->group(function () {
            Route::post('production/job-cards/{jobCard}/consume', [ProductionMaterialConsumptionController::class, 'store'])
                ->name('production.consume');
        });

        Route::middleware('permission:inventory.count.create')->group(function () {
            Route::get('stock-counts/create', [StockCountController::class, 'create'])->name('stock-counts.create');
            Route::post('stock-counts', [StockCountController::class, 'store'])->name('stock-counts.store');
        });

        Route::middleware('permission:inventory.count.view')->group(function () {
            Route::get('stock-counts', [StockCountController::class, 'index'])->name('stock-counts.index');
            Route::get('stock-counts/{stockCount}', [StockCountController::class, 'show'])->whereNumber('stockCount')->name('stock-counts.show');
            Route::get('stock-counts/{stockCount}/worksheet', [StockCountController::class, 'worksheet'])->whereNumber('stockCount')->name('stock-counts.worksheet');
            Route::get('stock-counts/{stockCount}/export', [StockCountController::class, 'exportWorksheet'])->whereNumber('stockCount')->name('stock-counts.export');
        });

        Route::middleware('permission:inventory.count.edit')->group(function () {
            Route::put('stock-counts/{stockCount}/worksheet', [StockCountController::class, 'updateWorksheet'])->whereNumber('stockCount')->name('stock-counts.worksheet.update');
            Route::post('stock-counts/{stockCount}/cancel', [StockCountController::class, 'cancel'])->whereNumber('stockCount')->name('stock-counts.cancel');
        });

        Route::middleware('permission:inventory.count.submit')->group(function () {
            Route::post('stock-counts/{stockCount}/submit', [StockCountController::class, 'submit'])->whereNumber('stockCount')->name('stock-counts.submit');
        });

        Route::middleware('permission:inventory.count.approve')->group(function () {
            Route::post('stock-counts/{stockCount}/approve', [StockCountController::class, 'approve'])->whereNumber('stockCount')->name('stock-counts.approve');
        });

        Route::middleware('permission:inventory.count.post')->group(function () {
            Route::post('stock-counts/{stockCount}/post', [StockCountController::class, 'post'])->whereNumber('stockCount')->name('stock-counts.post');
        });

        Route::middleware('permission:inventory.cycle.manage')->group(function () {
            Route::get('cycle-counts/create', [CycleCountController::class, 'create'])->name('cycle-counts.create');
            Route::post('cycle-counts', [CycleCountController::class, 'store'])->name('cycle-counts.store');
            Route::get('cycle-counts/{cycleCount}/edit', [CycleCountController::class, 'edit'])->whereNumber('cycleCount')->name('cycle-counts.edit');
            Route::put('cycle-counts/{cycleCount}', [CycleCountController::class, 'update'])->whereNumber('cycleCount')->name('cycle-counts.update');
            Route::post('cycle-counts/{cycleCount}/generate', [CycleCountController::class, 'generate'])->whereNumber('cycleCount')->name('cycle-counts.generate');
            Route::post('cycle-counts/{cycleCount}/deactivate', [CycleCountController::class, 'deactivate'])->whereNumber('cycleCount')->name('cycle-counts.deactivate');
        });

        Route::middleware('permission:inventory.cycle.view')->group(function () {
            Route::get('cycle-counts', [CycleCountController::class, 'index'])->name('cycle-counts.index');
            Route::get('cycle-counts/{cycleCount}', [CycleCountController::class, 'show'])->whereNumber('cycleCount')->name('cycle-counts.show');
        });

        Route::middleware('permission:inventory.variance.view')->group(function () {
            Route::get('variances', [InventoryVarianceController::class, 'index'])->name('variances.index');
            Route::get('variances/export', [InventoryVarianceController::class, 'export'])->name('variances.export');
            Route::get('variances/export-pdf', [InventoryVarianceController::class, 'exportPdf'])->name('variances.export-pdf');
        });

        Route::middleware('permission:inventory.reconcile.view')->group(function () {
            Route::get('reconciliations', [InventoryReconciliationController::class, 'index'])->name('reconciliations.index');
            Route::get('reconciliations/{reconciliation}', [InventoryReconciliationController::class, 'show'])->whereNumber('reconciliation')->name('reconciliations.show');
        });

        Route::middleware('permission:inventory.reconcile.approve')->group(function () {
            Route::post('reconciliations/{reconciliation}/approve', [InventoryReconciliationController::class, 'approve'])->whereNumber('reconciliation')->name('reconciliations.approve');
        });

        Route::middleware('permission:inventory.reconcile.post')->group(function () {
            Route::post('reconciliations/{reconciliation}/post', [InventoryReconciliationController::class, 'post'])->whereNumber('reconciliation')->name('reconciliations.post');
        });
    });

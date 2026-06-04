<?php

use App\Http\Controllers\Admin\Assets\AssetCategoryController;
use App\Http\Controllers\Admin\Assets\AssetDashboardController;
use App\Http\Controllers\Admin\Assets\AssetLifecycleController;
use App\Http\Controllers\Admin\Assets\FixedAssetController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/assets')
    ->name('admin.assets.')
    ->group(function () {
        Route::get('/', AssetDashboardController::class)
            ->middleware('permission:assets.view')
            ->name('dashboard');

        Route::middleware('permission:assets.view')->group(function () {
            Route::get('categories', [AssetCategoryController::class, 'index'])->name('categories.index');
            Route::get('register', [FixedAssetController::class, 'index'])->name('index');
            Route::get('register/{asset}', [FixedAssetController::class, 'show'])->name('show');
        });

        Route::middleware('permission:assets.create')->group(function () {
            Route::get('categories/create', [AssetCategoryController::class, 'create'])->name('categories.create');
            Route::post('categories', [AssetCategoryController::class, 'store'])->name('categories.store');
        });

        Route::middleware('permission:assets.create')->group(function () {
            Route::get('register/create', [FixedAssetController::class, 'create'])->name('create');
            Route::post('register', [FixedAssetController::class, 'store'])->name('store');
        });

        Route::middleware('permission:assets.manage')->group(function () {
            Route::get('register/{asset}/transfer', [AssetLifecycleController::class, 'transferForm'])->name('transfer');
            Route::post('register/{asset}/transfer', [AssetLifecycleController::class, 'transfer'])->name('transfer.store');
            Route::post('register/{asset}/maintenance', [AssetLifecycleController::class, 'maintenance'])->name('maintenance');
            Route::post('register/{asset}/repair', [AssetLifecycleController::class, 'repair'])->name('repair');
            Route::post('register/{asset}/repair-complete', [AssetLifecycleController::class, 'repairComplete'])->name('repair-complete');
            Route::get('register/{asset}/dispose', [AssetLifecycleController::class, 'disposeForm'])->name('dispose');
            Route::post('register/{asset}/dispose', [AssetLifecycleController::class, 'dispose'])->name('dispose.store');
            Route::post('register/{asset}/depreciate', [AssetLifecycleController::class, 'depreciate'])->name('depreciate');
        });

        Route::middleware('permission:assets.view')->group(function () {
            Route::get('register/{asset}/barcode', [AssetLifecycleController::class, 'barcode'])->name('barcode');
        });
    });

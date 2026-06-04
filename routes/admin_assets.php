<?php

use App\Http\Controllers\Admin\Assets\AssetDashboardController;
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
            Route::get('register', [FixedAssetController::class, 'index'])->name('index');
            Route::get('register/{asset}', [FixedAssetController::class, 'show'])->name('show');
        });

        Route::middleware('permission:assets.create')->group(function () {
            Route::get('register/create', [FixedAssetController::class, 'create'])->name('create');
            Route::post('register', [FixedAssetController::class, 'store'])->name('store');
        });
    });

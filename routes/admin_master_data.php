<?php

use App\Http\Controllers\Admin\MasterDataController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant', \App\Http\Middleware\CaptureWorkspaceNavigationQuery::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::prefix('master-data')->name('master-data.')->group(function () {
            Route::middleware('permission:configuration.master_data.view')->group(function () {
                Route::get('/', [MasterDataController::class, 'index'])->name('index');
            });

            Route::middleware('permission:configuration.master_data.export')->group(function () {
                Route::get('export/{format}', [MasterDataController::class, 'export'])
                    ->where('format', 'csv|excel|pdf')
                    ->name('export');
            });

            Route::middleware('permission:configuration.master_data.create')->group(function () {
                Route::get('create', [MasterDataController::class, 'create'])->name('create');
                Route::post('/', [MasterDataController::class, 'store'])->name('store');
            });

            Route::middleware('permission:configuration.master_data.import')->group(function () {
                Route::post('import', [MasterDataController::class, 'import'])->name('import');
            });

            Route::middleware('permission:configuration.master_data.view')->group(function () {
                Route::get('{masterDataValue}/dependencies', [MasterDataController::class, 'dependencies'])->name('dependencies');
            });

            Route::middleware('permission:configuration.master_data.edit')->group(function () {
                Route::get('{masterDataValue}/edit', [MasterDataController::class, 'edit'])->name('edit');
                Route::put('{masterDataValue}', [MasterDataController::class, 'update'])->name('update');
            });

            Route::middleware('permission:configuration.master_data.deactivate')->group(function () {
                Route::patch('{masterDataValue}/deactivate', [MasterDataController::class, 'deactivate'])->name('deactivate');
                Route::patch('{masterDataValue}/reactivate', [MasterDataController::class, 'reactivate'])->name('reactivate');
            });
        });
    });

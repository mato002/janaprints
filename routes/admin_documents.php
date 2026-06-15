<?php

use App\Http\Controllers\Admin\Documents\DocumentSettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::middleware('permission:documents.settings.view')->group(function () {
            Route::get('documents/settings', [DocumentSettingsController::class, 'index'])
                ->name('documents.settings.index');
        });

        Route::middleware('permission:documents.settings.edit')->group(function () {
            Route::put('documents/settings', [DocumentSettingsController::class, 'update'])
                ->name('documents.settings.update');
            Route::delete('documents/settings/{key}', [DocumentSettingsController::class, 'resetSetting'])
                ->where('key', '[A-Za-z0-9._-]+')
                ->name('documents.settings.reset');
        });
    });

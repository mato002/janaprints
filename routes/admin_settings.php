<?php

use App\Http\Controllers\Admin\ApprovalSettingsController;
use App\Http\Controllers\Admin\FormSettingsController;
use App\Http\Controllers\Admin\NumberingSettingsController;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::middleware('permission:settings.view')->group(function () {
            Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
            Route::get('settings/numbering', [NumberingSettingsController::class, 'index'])->name('settings.numbering.index');
            Route::get('settings/approvals', [ApprovalSettingsController::class, 'index'])->name('settings.approvals.index');
            Route::get('settings/forms', [FormSettingsController::class, 'index'])->name('settings.forms.index');
            Route::get('settings/{section}', [SettingsController::class, 'show'])->name('settings.show');
        });

        Route::middleware('permission:settings.manage')->group(function () {
            Route::put('settings/numbering', [NumberingSettingsController::class, 'update'])->name('settings.numbering.update');
            Route::put('settings/approvals', [ApprovalSettingsController::class, 'update'])->name('settings.approvals.update');
            Route::put('settings/forms', [FormSettingsController::class, 'update'])->name('settings.forms.update');
            Route::put('settings/{section}', [SettingsController::class, 'update'])->name('settings.update');
        });
    });

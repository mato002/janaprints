<?php

use App\Http\Controllers\Admin\Export\AdministrationListingExportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/administration')
    ->name('admin.administration.')
    ->group(function () {
        Route::get('exports/{listing}/{format}', [AdministrationListingExportController::class, 'download'])
            ->where('format', 'csv|excel|pdf')
            ->name('exports');
    });

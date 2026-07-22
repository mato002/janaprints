<?php

use App\Http\Controllers\Admin\Artwork\DesignerDeskController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/artwork')
    ->name('admin.artwork.')
    ->group(function () {
        Route::get('desk', [DesignerDeskController::class, 'index'])
            ->middleware('permission:artwork.view')
            ->name('desk');

        Route::get('desk/requests/{artworkRequest}/panel', [DesignerDeskController::class, 'panel'])
            ->middleware('permission:artwork.view')
            ->name('desk.panel');
    });

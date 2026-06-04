<?php

use App\Http\Controllers\Admin\Communications\CommunicationLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/communications/logs')
    ->name('admin.communications.logs.')
    ->group(function () {
        Route::middleware('permission:communications.logs.export')->group(function () {
            Route::get('export/download', [CommunicationLogController::class, 'export'])->name('export');
        });

        Route::middleware('permission:communications.logs.view')->group(function () {
            Route::get('/', [CommunicationLogController::class, 'dashboard'])->name('dashboard');
            Route::get('timeline', [CommunicationLogController::class, 'timeline'])->name('timeline');
            Route::get('search', [CommunicationLogController::class, 'search'])->name('search');
            Route::get('analytics', [CommunicationLogController::class, 'analytics'])->name('analytics');
            Route::get('failures', [CommunicationLogController::class, 'failures'])->name('failures');
            Route::get('{communicationLog}', [CommunicationLogController::class, 'show'])->name('show');
        });
    });

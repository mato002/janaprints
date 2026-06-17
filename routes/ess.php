<?php

use App\Http\Controllers\Ess\EssDocumentController;
use App\Http\Controllers\Ess\EssPayslipController;
use App\Http\Controllers\Ess\EssProfileController;
use App\Http\Controllers\Ess\EssSecurityController;
use App\Http\Controllers\Ess\EssWorkspaceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'employee.auth', 'tenant'])
    ->prefix('ess')
    ->name('ess.')
    ->group(function () {
        Route::get('/', EssWorkspaceController::class)->name('dashboard');

        Route::middleware('permission:ess.profile.update')->group(function () {
            Route::put('profile', [EssProfileController::class, 'update'])->name('profile.update');
        });

        Route::middleware('permission:ess.payslips.view')->group(function () {
            Route::get('payslips/{payslip}/download', [EssPayslipController::class, 'download'])
                ->name('payslips.download');
        });

        Route::middleware('permission:ess.documents.download')->group(function () {
            Route::get('documents/{document}/download', [EssDocumentController::class, 'download'])
                ->name('documents.download');
        });

        Route::prefix('security')->name('security.')->group(function () {
            Route::put('password', [EssSecurityController::class, 'updatePassword'])->name('password.update');
            Route::post('sessions/logout-others', [EssSecurityController::class, 'destroyOthers'])
                ->name('sessions.destroy-others');
        });
    });

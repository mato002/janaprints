<?php

use App\Http\Controllers\Admin\EmailIdentity\EmailIdentityController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/email-identity')
    ->name('admin.email-identity.')
    ->group(function () {
        Route::middleware('permission:integrations.view|employees.manage|integrations.manage')->group(function () {
            Route::get('/', [EmailIdentityController::class, 'index'])->name('index');
        });
    });

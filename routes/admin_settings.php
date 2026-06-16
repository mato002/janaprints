<?php

use App\Http\Controllers\Admin\ApprovalSettingsController;
use App\Http\Controllers\Admin\BrandingSettingsController;
use App\Http\Controllers\Admin\CompanyEmailController;
use App\Http\Controllers\Admin\DocumentTypesSettingsController;
use App\Http\Controllers\Admin\FormSettingsController;
use App\Http\Controllers\Admin\NumberingSettingsController;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::middleware('permission:configuration.document_types.view')->group(function () {
            Route::get('settings/document-types', [DocumentTypesSettingsController::class, 'index'])->name('settings.document-types.index');
            Route::get('settings/document-types/create', [DocumentTypesSettingsController::class, 'create'])->name('settings.document-types.create');
            Route::get('settings/document-types/{documentTypeDefinition}/edit', [DocumentTypesSettingsController::class, 'edit'])->name('settings.document-types.edit');
        });

        Route::middleware('permission:settings.view')->group(function () {
            Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
            Route::get('settings/branding', [BrandingSettingsController::class, 'edit'])->name('settings.branding.edit');
            Route::get('settings/numbering', [NumberingSettingsController::class, 'index'])->name('settings.numbering.index');
            Route::get('settings/approvals', [ApprovalSettingsController::class, 'index'])->name('settings.approvals.index');
            Route::get('settings/forms', [FormSettingsController::class, 'index'])->name('settings.forms.index');
            Route::get('settings/company-email', [CompanyEmailController::class, 'index'])->name('settings.company-email.index');
            Route::get('settings/company-email/create', [CompanyEmailController::class, 'create'])->name('settings.company-email.create');
            Route::get('settings/company-email/manage', [CompanyEmailController::class, 'show'])->name('settings.company-email.show');
            Route::get('settings/{section}', [SettingsController::class, 'show'])->name('settings.show');
        });

        Route::middleware('permission:configuration.document_types.create')->group(function () {
            Route::post('settings/document-types', [DocumentTypesSettingsController::class, 'store'])->name('settings.document-types.store');
        });

        Route::middleware('permission:configuration.document_types.edit')->group(function () {
            Route::put('settings/document-types/{documentTypeDefinition}', [DocumentTypesSettingsController::class, 'update'])->name('settings.document-types.update');
        });

        Route::middleware('permission:configuration.document_types.activate')->group(function () {
            Route::patch('settings/document-types/{documentTypeDefinition}/activate', [DocumentTypesSettingsController::class, 'activate'])->name('settings.document-types.activate');
        });

        Route::middleware('permission:configuration.document_types.deactivate')->group(function () {
            Route::patch('settings/document-types/{documentTypeDefinition}/deactivate', [DocumentTypesSettingsController::class, 'deactivate'])->name('settings.document-types.deactivate');
        });

        Route::middleware('permission:settings.manage')->group(function () {
            Route::put('settings/branding', [BrandingSettingsController::class, 'update'])->name('settings.branding.update');
            Route::put('settings/numbering', [NumberingSettingsController::class, 'update'])->name('settings.numbering.update');
            Route::put('settings/approvals', [ApprovalSettingsController::class, 'update'])->name('settings.approvals.update');
            // Must stay before settings/{section} so PUT /settings/forms hits the forms controller.
            Route::put('settings/forms', [FormSettingsController::class, 'update'])->name('settings.forms.update');
            Route::post('settings/company-email', [CompanyEmailController::class, 'store'])->name('settings.company-email.store');
            Route::post('settings/company-email/test-connection', [CompanyEmailController::class, 'testConnection'])->name('settings.company-email.test-connection');
            Route::put('settings/company-email/password', [CompanyEmailController::class, 'updatePassword'])->name('settings.company-email.update-password');
            Route::put('settings/company-email/quota', [CompanyEmailController::class, 'updateQuota'])->name('settings.company-email.update-quota');
            Route::delete('settings/company-email', [CompanyEmailController::class, 'destroy'])->name('settings.company-email.destroy');
            Route::put('settings/{section}', [SettingsController::class, 'update'])
                ->name('settings.update')
                ->where('section', '^(?!forms$).*');
        });
    });

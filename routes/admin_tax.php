<?php

use App\Http\Controllers\Admin\Tax\TaxAuditLogController;
use App\Http\Controllers\Admin\Tax\TaxCodeController;
use App\Http\Controllers\Admin\Tax\TaxLedgerController;
use App\Http\Controllers\Admin\Tax\TaxPeriodController;
use App\Http\Controllers\Admin\Tax\TaxReportController;
use App\Http\Controllers\Admin\Tax\TaxReturnController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/tax')
    ->name('admin.tax.')
    ->group(function () {
        Route::prefix('codes')
            ->name('codes.')
            ->group(function () {
                Route::middleware('permission:tax.codes.view')->group(function () {
                    Route::get('/', [TaxCodeController::class, 'index'])->name('index');
                });

                Route::middleware('permission:tax.codes.manage')->group(function () {
                    Route::get('create', [TaxCodeController::class, 'create'])->name('create');
                    Route::post('/', [TaxCodeController::class, 'store'])->name('store');
                });

                Route::middleware('permission:tax.codes.view')->group(function () {
                    Route::get('{taxCode}', [TaxCodeController::class, 'show'])->name('show');
                });

                Route::middleware('permission:tax.codes.manage')->group(function () {
                    Route::get('{taxCode}/edit', [TaxCodeController::class, 'edit'])->name('edit');
                    Route::put('{taxCode}', [TaxCodeController::class, 'update'])->name('update');
                    Route::post('{taxCode}/rates', [TaxCodeController::class, 'storeRate'])->name('rates.store');
                });
            });

        Route::middleware('permission:tax.periods.view')->group(function () {
            Route::get('periods', [TaxPeriodController::class, 'index'])->name('periods.index');
        });

        Route::prefix('returns')
            ->name('returns.')
            ->middleware('permission:tax.returns.manage')
            ->group(function () {
                Route::get('/', [TaxReturnController::class, 'index'])->name('index');
                Route::get('{taxReturn}', [TaxReturnController::class, 'show'])->name('show');
                Route::get('{taxReturn}/package', [TaxReturnController::class, 'downloadPackage'])->name('package');
                Route::post('periods/{taxPeriod}/draft', [TaxReturnController::class, 'buildDraft'])->name('draft');
                Route::post('{taxReturn}/file', [TaxReturnController::class, 'file'])->name('file');
            });

        Route::prefix('reports')
            ->name('reports.')
            ->middleware('permission:tax.reports.view')
            ->group(function () {
                Route::get('vat-summary', [TaxReportController::class, 'vatSummary'])->name('vat-summary');
                Route::get('output-vat', [TaxReportController::class, 'outputVat'])->name('output-vat');
                Route::get('input-vat', [TaxReportController::class, 'inputVat'])->name('input-vat');
                Route::get('liability', [TaxReportController::class, 'liability'])->name('liability');
            });

        Route::middleware('permission:tax.ledger.view')->group(function () {
            Route::get('ledger', [TaxLedgerController::class, 'index'])->name('ledger.index');
        });

        Route::middleware('permission:tax.audit.view')->group(function () {
            Route::get('audit', [TaxAuditLogController::class, 'index'])->name('audit.index');
        });
    });

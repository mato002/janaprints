<?php

use App\Http\Controllers\Admin\Sales\QuotationAttachmentController;
use App\Http\Controllers\Admin\Sales\QuotationController;
use App\Http\Controllers\Admin\Sales\QuotationDashboardController;
use App\Http\Controllers\Admin\Sales\QuotationDocumentController;
use App\Http\Controllers\Admin\Sales\QuotationNoteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/quotations')
    ->name('admin.quotations.')
    ->group(function () {
        Route::get('/', QuotationDashboardController::class)
            ->middleware('permission:quotations.view')
            ->name('dashboard');

        Route::middleware('permission:quotations.view')->group(function () {
            Route::get('list', [QuotationController::class, 'index'])->name('index');
        });

        Route::middleware('permission:quotations.create')->group(function () {
            Route::get('list/create', [QuotationController::class, 'create'])->name('create');
            Route::post('list', [QuotationController::class, 'store'])->name('store');
        });

        Route::middleware('permission:quotations.view')->group(function () {
            Route::get('list/{quotation}', [QuotationController::class, 'show'])->name('show');
            Route::get('list/{quotation}/document', [QuotationDocumentController::class, 'show'])->name('document');
            Route::get('list/{quotation}/document/pdf', [QuotationDocumentController::class, 'pdf'])->name('document.pdf');
        });

        Route::middleware('permission:quotations.edit')->group(function () {
            Route::get('list/{quotation}/edit', [QuotationController::class, 'edit'])->name('edit');
            Route::put('list/{quotation}', [QuotationController::class, 'update'])->name('update');
            Route::post('list/{quotation}/submit-approval', [QuotationController::class, 'submitForApproval'])->name('submit-approval');
            Route::post('list/{quotation}/mark-viewed', [QuotationController::class, 'markViewed'])->name('mark-viewed');
            Route::post('list/{quotation}/accept', [QuotationController::class, 'accept'])->name('accept');
            Route::post('list/{quotation}/reject', [QuotationController::class, 'reject'])->name('reject');
            Route::post('list/{quotation}/expire', [QuotationController::class, 'expire'])->name('expire');
            Route::post('list/{quotation}/notes', [QuotationNoteController::class, 'store'])->name('notes.store');
            Route::delete('list/{quotation}/notes/{note}', [QuotationNoteController::class, 'destroy'])->name('notes.destroy');
            Route::post('list/{quotation}/attachments', [QuotationAttachmentController::class, 'store'])->name('attachments.store');
            Route::delete('list/{quotation}/attachments/{attachment}', [QuotationAttachmentController::class, 'destroy'])->name('attachments.destroy');
        });

        Route::middleware('permission:quotations.delete')->group(function () {
            Route::delete('list/{quotation}', [QuotationController::class, 'destroy'])->name('destroy');
        });

        Route::middleware('permission:quotations.view')->group(function () {
            Route::post('list/{quotation}/approve', [QuotationController::class, 'approve'])
                ->middleware('can:approve,quotation')
                ->name('approve');
        });

        Route::middleware('permission:quotations.send')->group(function () {
            Route::post('list/{quotation}/send', [QuotationController::class, 'send'])->name('send');
        });

        Route::middleware('permission:quotations.convert')->group(function () {
            Route::post('list/{quotation}/convert', [QuotationController::class, 'convert'])->name('convert');
        });
    });

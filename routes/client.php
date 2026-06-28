<?php

use App\Http\Controllers\Client\ClientAccountController;
use App\Http\Controllers\Client\ClientArtworkController;
use App\Http\Controllers\Client\ClientArtworkLibraryController;
use App\Http\Controllers\Client\ClientInboxController;
use App\Http\Controllers\Client\ClientDashboardController;
use App\Http\Controllers\Client\ClientInvoiceController;
use App\Http\Controllers\Client\ClientOrderController;
use App\Http\Controllers\Client\ClientPaymentController;
use App\Http\Controllers\Client\ClientQuotationController;
use App\Http\Controllers\Client\ClientRepeatOrderController;
use App\Http\Controllers\Client\ClientStatementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'client.auth', 'tenant'])
    ->prefix('client')
    ->name('client.')
    ->group(function () {
        Route::get('/', ClientDashboardController::class)->name('dashboard');

        Route::get('quotations', [ClientQuotationController::class, 'index'])->name('quotations.index');
        Route::get('quotations/{quotation}', [ClientQuotationController::class, 'show'])->name('quotations.show');
        Route::get('quotations/{quotation}/pdf', [ClientQuotationController::class, 'pdf'])->name('quotations.pdf');
        Route::post('quotations/{quotation}/accept', [ClientQuotationController::class, 'accept'])->name('quotations.accept');
        Route::post('quotations/{quotation}/reject', [ClientQuotationController::class, 'reject'])->name('quotations.reject');

        Route::get('orders', [ClientOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [ClientOrderController::class, 'show'])->name('orders.show');

        Route::get('invoices', [ClientInvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/{invoice}', [ClientInvoiceController::class, 'show'])->name('invoices.show');
        Route::get('invoices/{invoice}/pdf', [ClientInvoiceController::class, 'pdf'])->name('invoices.pdf');

        Route::get('payments', [ClientPaymentController::class, 'index'])->name('payments.index');

        Route::get('statements', [ClientStatementController::class, 'index'])->name('statements.index');
        Route::get('statements/download', [ClientStatementController::class, 'download'])->name('statements.download');

        Route::get('artwork-library', fn () => redirect()->route('client.artwork.index'))->name('artwork-library.index');
        Route::post('artwork-library', [ClientArtworkLibraryController::class, 'store'])->name('artwork-library.store');
        Route::get('artwork-library/{libraryArtwork}/preview', [ClientArtworkLibraryController::class, 'preview'])->name('artwork-library.preview');
        Route::get('artwork-library/{libraryArtwork}/download', [ClientArtworkLibraryController::class, 'download'])->name('artwork-library.download');

        Route::get('repeat-orders', [ClientRepeatOrderController::class, 'index'])->name('repeat-orders.index');
        Route::post('repeat-orders/{order}', [ClientRepeatOrderController::class, 'store'])->name('repeat-orders.store');

        Route::get('communications/unread', [ClientInboxController::class, 'unread'])->name('communications.unread');
        Route::get('communications/feed', [ClientInboxController::class, 'feed'])->name('communications.feed');
        Route::get('communications', [ClientInboxController::class, 'index'])->name('communications.index');
        Route::post('communications/messages', [ClientInboxController::class, 'storeMessage'])->name('communications.messages.store');
        Route::post('communications/attachments', [ClientInboxController::class, 'storeAttachment'])->name('communications.attachments.store');
        Route::get('communications/{conversation}/attachments/{attachment}/download', [ClientInboxController::class, 'downloadAttachment'])
            ->name('communications.attachments.download');

        Route::get('artwork', [ClientArtworkController::class, 'index'])->name('artwork.index');
        Route::get('artwork/{artwork}', [ClientArtworkController::class, 'show'])->name('artwork.show');
        Route::get('artwork/{artwork}/file', [ClientArtworkController::class, 'file'])->name('artwork.file');
        Route::post('artwork/{artwork}/review', [ClientArtworkController::class, 'review'])->name('artwork.review');

        Route::get('account', [ClientAccountController::class, 'edit'])->name('account.edit');
        Route::put('account', [ClientAccountController::class, 'update'])->name('account.update');
    });

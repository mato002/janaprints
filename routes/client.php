<?php

use App\Http\Controllers\Client\ClientAccountController;
use App\Http\Controllers\Client\ClientArtworkController;
use App\Http\Controllers\Client\ClientDashboardController;
use App\Http\Controllers\Client\ClientInvoiceController;
use App\Http\Controllers\Client\ClientOrderController;
use App\Http\Controllers\Client\ClientPaymentController;
use App\Http\Controllers\Client\ClientQuotationController;
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

        Route::get('artwork', [ClientArtworkController::class, 'index'])->name('artwork.index');
        Route::get('artwork/{artwork}', [ClientArtworkController::class, 'show'])->name('artwork.show');
        Route::post('artwork/{artwork}/review', [ClientArtworkController::class, 'review'])->name('artwork.review');

        Route::get('account', [ClientAccountController::class, 'edit'])->name('account.edit');
        Route::put('account', [ClientAccountController::class, 'update'])->name('account.update');
    });

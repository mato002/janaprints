<?php

use App\Http\Controllers\Admin\CustomerService\PublicContactMessageController;
use App\Http\Controllers\Admin\CustomerService\PublicQuoteRequestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::middleware('permission:public_leads.quote_requests.view')->group(function () {
            Route::get('public-quote-requests', [PublicQuoteRequestController::class, 'index'])
                ->name('public-quote-requests.index');
            Route::get('public-quote-requests/{publicQuoteRequest}', [PublicQuoteRequestController::class, 'show'])
                ->name('public-quote-requests.show');
            Route::get('public-quote-requests/{publicQuoteRequest}/artwork', [PublicQuoteRequestController::class, 'downloadArtwork'])
                ->name('public-quote-requests.artwork');
            Route::get('public-quote-requests/{publicQuoteRequest}/artwork/preview', [PublicQuoteRequestController::class, 'previewArtwork'])
                ->name('public-quote-requests.artwork-preview');
        });

        Route::middleware('permission:public_leads.quote_requests.manage')->group(function () {
            Route::patch('public-quote-requests/{publicQuoteRequest}/status', [PublicQuoteRequestController::class, 'updateStatus'])
                ->name('public-quote-requests.update-status');
            Route::patch('public-quote-requests/{publicQuoteRequest}/review', [PublicQuoteRequestController::class, 'updateReview'])
                ->name('public-quote-requests.update-review');
            Route::post('public-quote-requests/{publicQuoteRequest}/notes', [PublicQuoteRequestController::class, 'storeNote'])
                ->name('public-quote-requests.notes.store');
            Route::patch('public-quote-requests/{publicQuoteRequest}/notes', [PublicQuoteRequestController::class, 'updateNotes'])
                ->name('public-quote-requests.update-notes');
        });

        Route::middleware('permission:public_leads.contact_messages.view')->group(function () {
            Route::get('public-contact-messages', [PublicContactMessageController::class, 'index'])
                ->name('public-contact-messages.index');
            Route::get('public-contact-messages/{publicContactMessage}', [PublicContactMessageController::class, 'show'])
                ->name('public-contact-messages.show');
        });

        Route::middleware('permission:public_leads.contact_messages.manage')->group(function () {
            Route::patch('public-contact-messages/{publicContactMessage}/status', [PublicContactMessageController::class, 'updateStatus'])
                ->name('public-contact-messages.update-status');
            Route::patch('public-contact-messages/{publicContactMessage}/notes', [PublicContactMessageController::class, 'updateNotes'])
                ->name('public-contact-messages.update-notes');
        });
    });

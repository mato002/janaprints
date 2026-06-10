<?php

use App\Http\Controllers\Admin\CustomerService\PublicContactMessageController;
use App\Http\Controllers\Admin\CustomerService\PublicQuoteRequestController;
use App\Http\Controllers\Admin\CustomerService\QrArtworkAnalysisController;
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

            Route::get('public-quote-requests/{publicQuoteRequest}/artwork/{artworkFile}/printing-analysis/modal', [QrArtworkAnalysisController::class, 'showModal'])
                ->middleware('permission:printing.intelligence.view')
                ->name('public-quote-requests.printing-analysis.modal');
        });

        Route::middleware('permission:printing.artwork.analyze')->group(function () {
            Route::post('public-quote-requests/{publicQuoteRequest}/artwork/{artworkFile}/printing-analysis', [QrArtworkAnalysisController::class, 'run'])
                ->name('public-quote-requests.printing-analysis.run');
            Route::post('public-quote-requests/{publicQuoteRequest}/artwork/{artworkFile}/printing-analysis/rerun', [QrArtworkAnalysisController::class, 'rerun'])
                ->name('public-quote-requests.printing-analysis.rerun');
            Route::post('public-quote-requests/{publicQuoteRequest}/artwork/{artworkFile}/printing-analysis/metadata', [QrArtworkAnalysisController::class, 'runMetadata'])
                ->name('public-quote-requests.printing-analysis.metadata');
        });

        Route::middleware('permission:printing.artwork.colour-analyze')->group(function () {
            Route::post('public-quote-requests/{publicQuoteRequest}/artwork/{artworkFile}/printing-analysis/colour', [QrArtworkAnalysisController::class, 'runColour'])
                ->name('public-quote-requests.printing-analysis.colour');
        });

        Route::middleware('permission:printing.artwork.estimate-ink')->group(function () {
            Route::post('public-quote-requests/{publicQuoteRequest}/artwork/{artworkFile}/printing-analysis/ink', [QrArtworkAnalysisController::class, 'runInk'])
                ->name('public-quote-requests.printing-analysis.ink');
        });

        Route::middleware('permission:printing.artwork.estimate-production')->group(function () {
            Route::post('public-quote-requests/{publicQuoteRequest}/artwork/{artworkFile}/printing-analysis/production', [QrArtworkAnalysisController::class, 'runProduction'])
                ->name('public-quote-requests.printing-analysis.production');
        });

        Route::middleware('permission:printing.quotation.estimate')->group(function () {
            Route::post('public-quote-requests/{publicQuoteRequest}/artwork/{artworkFile}/printing-analysis/quotation', [QrArtworkAnalysisController::class, 'runQuotationEstimate'])
                ->name('public-quote-requests.printing-analysis.quotation');
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

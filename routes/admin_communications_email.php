<?php

use App\Http\Controllers\Admin\Communications\Email\EmailAnalyticsController;
use App\Http\Controllers\Admin\Communications\Email\EmailCampaignController;
use App\Http\Controllers\Admin\Communications\Email\EmailCertificationController;
use App\Http\Controllers\Admin\Communications\Email\EmailCommunicationReportsController;
use App\Http\Controllers\Admin\Communications\Email\EmailComposerController;
use App\Http\Controllers\Admin\Communications\Email\EmailDashboardController;
use App\Http\Controllers\Admin\Communications\Email\EmailDeliveryController;
use App\Http\Controllers\Admin\Communications\Email\EmailInboxController;
use App\Http\Controllers\Admin\Communications\Email\EmailMessageOperationsController;
use App\Http\Controllers\Admin\Communications\Email\EmailQueueController;
use App\Http\Controllers\Admin\Communications\Email\EmailSentController;
use App\Http\Controllers\Admin\Communications\Email\EmailSettingsController;
use App\Http\Controllers\Admin\Communications\Email\EmailTemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/communications/email')
    ->name('admin.communications.email.')
    ->group(function () {
        Route::middleware('permission:communications.email.view')->group(function () {
            Route::get('/', [EmailDashboardController::class, 'index'])->name('dashboard');
            Route::get('inbox', [EmailInboxController::class, 'index'])->name('inbox.index');
            Route::get('queue', [EmailQueueController::class, 'index'])->name('queue.index');
            Route::get('sent', [EmailSentController::class, 'index'])->name('sent.index');
            Route::get('campaigns', [EmailCampaignController::class, 'index'])->name('campaigns.index');
            Route::get('templates', [EmailTemplateController::class, 'index'])->name('templates.index');
            Route::get('analytics', [EmailAnalyticsController::class, 'index'])->name('analytics');
            Route::get('reports', [EmailCommunicationReportsController::class, 'index'])->name('reports.index');
            Route::get('campaigns/{emailCampaign}', [EmailCampaignController::class, 'show'])->name('campaigns.show');
            Route::get('messages/{emailMessage}', [EmailMessageOperationsController::class, 'show'])->name('messages.show');
        });

        Route::middleware('permission:communications.email.send')->group(function () {
            Route::get('compose', [EmailComposerController::class, 'create'])->name('compose');
            Route::post('compose', [EmailComposerController::class, 'store'])->name('compose.store');
            Route::post('compose/preview', [EmailComposerController::class, 'preview'])->name('compose.preview');
            Route::get('campaigns/create', [EmailCampaignController::class, 'create'])->name('campaigns.create');
            Route::post('campaigns', [EmailCampaignController::class, 'store'])->name('campaigns.store');
            Route::post('campaigns/{emailCampaign}/send', [EmailCampaignController::class, 'send'])->name('campaigns.send');
            Route::post('messages/{emailMessage}/send', [EmailComposerController::class, 'sendDraft'])->name('messages.send');
            Route::post('messages/{emailMessage}/cancel', [EmailMessageOperationsController::class, 'cancel'])->name('messages.cancel');
            Route::post('messages/{emailMessage}/retry', [EmailMessageOperationsController::class, 'retry'])->name('messages.retry');
        });

        Route::middleware('permission:communications.email.manage')->group(function () {
            Route::get('settings', [EmailSettingsController::class, 'index'])->name('settings');
            Route::get('certification', [EmailCertificationController::class, 'index'])->name('certification');
            Route::post('templates/sync', [EmailTemplateController::class, 'sync'])->name('templates.sync');
        });

        Route::middleware('permission:communications.email.audit')->group(function () {
            Route::get('delivery', [EmailDeliveryController::class, 'index'])->name('delivery.index');
            Route::get('delivery/{emailMessage}', [EmailDeliveryController::class, 'show'])->name('delivery.show');
        });
    });

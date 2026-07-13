<?php

use App\Http\Controllers\Admin\Communications\Sms\SmsCampaignController;
use App\Http\Controllers\Admin\Communications\Sms\SmsCreditController;
use App\Http\Controllers\Admin\Communications\Sms\SmsDashboardController;
use App\Http\Controllers\Admin\Communications\Sms\SmsProviderLogController;
use App\Http\Controllers\Admin\Communications\Sms\SmsQueueController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/communications/sms')
    ->name('admin.communications.sms.')
    ->group(function () {
        Route::middleware('permission:communications.sms.view')->group(function () {
            Route::get('/', [SmsDashboardController::class, 'index'])->name('dashboard');
            Route::get('campaigns', [SmsCampaignController::class, 'index'])->name('campaigns.index');
            Route::get('queues', [SmsQueueController::class, 'index'])->name('queues.index');
            Route::get('credits', [SmsCreditController::class, 'index'])->name('credits.index');
        });

        Route::middleware('permission:communications.sms.send')->group(function () {
            Route::get('campaigns/create', [SmsCampaignController::class, 'create'])->name('campaigns.create');
            Route::post('campaigns', [SmsCampaignController::class, 'store'])->name('campaigns.store');
            Route::post('campaigns/preview', [SmsCampaignController::class, 'preview'])->name('campaigns.preview');
            Route::post('campaigns/estimate-recipients', [SmsCampaignController::class, 'estimateRecipients'])->name('campaigns.estimate-recipients');
        });

        Route::middleware('permission:communications.sms.view')->group(function () {
            Route::get('campaigns/{campaign}', [SmsCampaignController::class, 'show'])->name('campaigns.show');
        });

        Route::middleware('permission:communications.sms.audit')->group(function () {
            Route::get('provider-logs', [SmsProviderLogController::class, 'index'])->name('provider-logs.index');
            Route::post('credits/topup', [SmsCreditController::class, 'topup'])->name('credits.topup');
            Route::get('credits/topup/{reference}/status', [SmsCreditController::class, 'topupStatus'])->name('credits.topup.status');
        });

        Route::middleware('permission:communications.sms.send')->group(function () {
            Route::get('campaigns/{campaign}/edit', [SmsCampaignController::class, 'edit'])->name('campaigns.edit');
            Route::put('campaigns/{campaign}', [SmsCampaignController::class, 'update'])->name('campaigns.update');
            Route::post('campaigns/{campaign}/send', [SmsCampaignController::class, 'send'])->name('campaigns.send');
            Route::post('campaigns/{campaign}/cancel', [SmsCampaignController::class, 'cancel'])->name('campaigns.cancel');
        });

        Route::middleware('permission:communications.sms.approve')->group(function () {
            Route::post('campaigns/{campaign}/approve', [SmsCampaignController::class, 'approve'])->name('campaigns.approve');
        });
    });

<?php

use App\Http\Controllers\Admin\Communications\Whatsapp\WhatsappAnalyticsController;
use App\Http\Controllers\Admin\Communications\Whatsapp\WhatsappConversationController;
use App\Http\Controllers\Admin\Communications\Whatsapp\WhatsappDeliveryController;
use App\Http\Controllers\Admin\Communications\Whatsapp\WhatsappInboxController;
use App\Http\Controllers\Admin\Communications\Whatsapp\WhatsappQueueController;
use App\Http\Controllers\Admin\Communications\Whatsapp\WhatsappTemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/communications/whatsapp')
    ->name('admin.communications.whatsapp.')
    ->group(function () {
        Route::middleware('permission:communications.whatsapp.view')->group(function () {
            Route::get('/', [WhatsappInboxController::class, 'index'])->name('inbox');
            Route::get('conversations', [WhatsappConversationController::class, 'index'])->name('conversations.index');
            Route::get('templates', [WhatsappTemplateController::class, 'index'])->name('templates.index');
            Route::get('queue', [WhatsappQueueController::class, 'index'])->name('queue.index');
            Route::get('analytics', [WhatsappAnalyticsController::class, 'index'])->name('analytics');
            Route::get('conversations/{conversation}', [WhatsappConversationController::class, 'show'])->name('conversations.show');
        });

        Route::middleware('permission:communications.whatsapp.send')->group(function () {
            Route::post('conversations/{conversation}/messages', [WhatsappConversationController::class, 'storeMessage'])
                ->name('conversations.messages.store');
        });

        Route::middleware('permission:communications.whatsapp.manage')->group(function () {
            Route::patch('conversations/{conversation}', [WhatsappConversationController::class, 'update'])
                ->name('conversations.update');
            Route::post('templates/sync', [WhatsappTemplateController::class, 'sync'])->name('templates.sync');
        });

        Route::middleware('permission:communications.whatsapp.audit')->group(function () {
            Route::get('delivery', [WhatsappDeliveryController::class, 'index'])->name('delivery.index');
            Route::get('delivery/{message}', [WhatsappDeliveryController::class, 'show'])->name('delivery.show');
        });
    });

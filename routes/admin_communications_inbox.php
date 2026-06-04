<?php

use App\Http\Controllers\Admin\Communications\Inbox\InboxController;
use App\Http\Controllers\Admin\Communications\Inbox\InboxExecutiveController;
use App\Http\Controllers\Admin\Communications\Inbox\InboxTeamController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/communications/inbox')
    ->name('admin.communications.inbox.')
    ->group(function () {
        Route::middleware('permission:communications.inbox.view')->group(function () {
            Route::get('/', [InboxController::class, 'index'])->name('index');
            Route::get('team', [InboxTeamController::class, 'index'])->name('team');
            Route::post('start', [InboxController::class, 'startFromPicker'])->name('start');
            Route::post('customers/{customer}/start', [InboxController::class, 'startCustomer'])->name('customers.start');
            Route::get('{inboxConversation}/attachments/{attachment}/download', [InboxController::class, 'downloadAttachment'])
                ->name('attachments.download');
        });

        Route::middleware('permission:communications.inbox.executive')->group(function () {
            Route::get('executive', [InboxExecutiveController::class, 'index'])->name('executive');
        });

        Route::middleware('permission:communications.inbox.reply')->group(function () {
            Route::post('{inboxConversation}/reply', [InboxController::class, 'reply'])->name('reply');
            Route::delete('{inboxConversation}/messages/{message}', [InboxController::class, 'destroyMessage'])->name('messages.destroy');
        });

        Route::middleware('permission:communications.inbox.notes')->group(function () {
            Route::post('{inboxConversation}/notes', [InboxController::class, 'storeNote'])->name('notes.store');
        });

        Route::middleware('permission:communications.inbox.view')->group(function () {
            Route::post('{inboxConversation}/tags', [InboxController::class, 'updateTags'])->name('tags.update');
        });

        Route::middleware('permission:communications.inbox.assign')->group(function () {
            Route::post('{inboxConversation}/assign', [InboxController::class, 'assign'])->name('assign');
        });

        Route::middleware('permission:communications.inbox.close')->group(function () {
            Route::post('{inboxConversation}/status', [InboxController::class, 'updateStatus'])->name('status');
        });

        Route::middleware('permission:communications.inbox.attachments')->group(function () {
            Route::post('{inboxConversation}/attachments', [InboxController::class, 'storeAttachment'])->name('attachments.store');
            Route::delete('{inboxConversation}/attachments/{attachment}', [InboxController::class, 'destroyAttachment'])->name('attachments.destroy');
        });
    });

<?php

use App\Http\Controllers\Admin\Communications\CommunicationTemplateController;
use App\Http\Controllers\Admin\Communications\NotificationBellController;
use App\Http\Controllers\Admin\Communications\NotificationCenterController;
use App\Http\Controllers\Admin\Communications\NotificationPreferenceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/communications')
    ->name('admin.communications.')
    ->group(function () {
        Route::middleware('permission:communications.templates.view')->group(function () {
            Route::get('templates', [CommunicationTemplateController::class, 'index'])->name('templates.index');
            Route::get('templates/{template}', [CommunicationTemplateController::class, 'show'])->name('templates.show');
        });

        Route::middleware('permission:communications.templates.create')->group(function () {
            Route::post('templates', [CommunicationTemplateController::class, 'store'])->name('templates.store');
        });

        Route::middleware('permission:communications.templates.edit')->group(function () {
            Route::put('templates/{template}', [CommunicationTemplateController::class, 'update'])->name('templates.update');
        });

        Route::middleware('permission:communications.templates.version_view')->group(function () {
            Route::get('templates/{template}/versions', [CommunicationTemplateController::class, 'versions'])->name('templates.versions');
            Route::get('templates/{template}/compare', [CommunicationTemplateController::class, 'compare'])->name('templates.compare');
        });

        Route::middleware('permission:communications.templates.restore')->group(function () {
            Route::post('templates/{template}/restore', [CommunicationTemplateController::class, 'restore'])->name('templates.restore');
        });

        Route::middleware('permission:communications.notifications.view')->group(function () {
            Route::get('notifications', [NotificationCenterController::class, 'index'])->name('notifications.index');
            Route::get('notifications/bell/panel', [NotificationBellController::class, 'panel'])->name('notifications.bell.panel');
            Route::get('notifications/bell/unread-count', [NotificationBellController::class, 'unreadCount'])->name('notifications.bell.unread');
            Route::post('notifications/mark-all-read', [NotificationBellController::class, 'markAllRead'])->name('notifications.mark-all-read');
            Route::post('notifications/{notification}/open', [NotificationBellController::class, 'open'])->name('notifications.open');
        });

        Route::middleware('permission:communications.notifications.manage')->group(function () {
            Route::post('notifications', [NotificationCenterController::class, 'store'])->name('notifications.store');
            Route::post('notifications/{notification}/read', [NotificationCenterController::class, 'markRead'])->name('notifications.mark-read');
            Route::post('notifications/{notification}/dismiss', [NotificationCenterController::class, 'dismiss'])->name('notifications.dismiss');
            Route::post('notifications/{notification}/archive', [NotificationCenterController::class, 'archive'])->name('notifications.archive');
            Route::post('notifications/bulk/read', [NotificationCenterController::class, 'bulkRead'])->name('notifications.bulk-read');
            Route::post('notifications/bulk/dismiss', [NotificationCenterController::class, 'bulkDismiss'])->name('notifications.bulk-dismiss');
            Route::post('notifications/{notification}/bell/read', [NotificationBellController::class, 'markRead'])->name('notifications.bell.mark-read');
            Route::put('notifications/preferences', [NotificationPreferenceController::class, 'update'])->name('notifications.preferences.update');
        });
    });

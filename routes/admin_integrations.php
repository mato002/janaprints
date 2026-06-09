<?php

use App\Http\Controllers\Admin\Integrations\IntegrationApiKeyController;
use App\Http\Controllers\Admin\Integrations\IntegrationEmailController;
use App\Http\Controllers\Admin\Integrations\IntegrationProviderController;
use App\Http\Controllers\Admin\Integrations\IntegrationSmsController;
use App\Http\Controllers\Admin\Integrations\IntegrationWebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/integrations')
    ->name('admin.integrations.')
    ->group(function () {
        Route::middleware('permission:integrations.email.manage|integrations.manage')->group(function () {
            Route::get('email/create', [IntegrationEmailController::class, 'create'])->name('email.create');
            Route::post('email', [IntegrationEmailController::class, 'store'])->name('email.store');
        });

        Route::middleware('permission:integrations.view|integrations.email.manage|integrations.manage')->group(function () {
            Route::get('email', [IntegrationEmailController::class, 'index'])->name('email.index');
        });

        Route::middleware('permission:integrations.email.manage|integrations.manage')->group(function () {
            Route::get('email/{emailSetting}/edit', [IntegrationEmailController::class, 'edit'])->name('email.edit');
            Route::put('email/{emailSetting}', [IntegrationEmailController::class, 'update'])->name('email.update');
            Route::post('email/{emailSetting}/test-connection', [IntegrationEmailController::class, 'testConnection'])->name('email.test-connection');
            Route::post('email/{emailSetting}/send-test', [IntegrationEmailController::class, 'sendTestEmail'])->name('email.send-test');
            Route::post('email/{emailSetting}/activate', [IntegrationEmailController::class, 'activate'])->name('email.activate');
            Route::post('email/{emailSetting}/deactivate', [IntegrationEmailController::class, 'deactivate'])->name('email.deactivate');
        });

        Route::middleware('permission:integrations.view|integrations.email.manage|integrations.manage')->group(function () {
            Route::get('email/{emailSetting}', [IntegrationEmailController::class, 'show'])->name('email.show');
        });

        Route::middleware('permission:integrations.sms.manage|integrations.manage')->group(function () {
            Route::get('sms/create', [IntegrationSmsController::class, 'create'])->name('sms.create');
            Route::post('sms', [IntegrationSmsController::class, 'store'])->name('sms.store');
        });

        Route::middleware('permission:integrations.view|integrations.sms.manage|integrations.manage')->group(function () {
            Route::get('sms', [IntegrationSmsController::class, 'index'])->name('sms.index');
        });

        Route::middleware('permission:integrations.sms.manage|integrations.manage')->group(function () {
            Route::get('sms/{smsSetting}/edit', [IntegrationSmsController::class, 'edit'])->name('sms.edit');
            Route::put('sms/{smsSetting}', [IntegrationSmsController::class, 'update'])->name('sms.update');
            Route::post('sms/{smsSetting}/verify', [IntegrationSmsController::class, 'verify'])->name('sms.verify');
            Route::post('sms/{smsSetting}/send-test', [IntegrationSmsController::class, 'sendTest'])->name('sms.send-test');
            Route::post('sms/{smsSetting}/activate', [IntegrationSmsController::class, 'activate'])->name('sms.activate');
            Route::post('sms/{smsSetting}/deactivate', [IntegrationSmsController::class, 'deactivate'])->name('sms.deactivate');
        });

        Route::middleware('permission:integrations.view|integrations.sms.manage|integrations.manage')->group(function () {
            Route::get('sms/{smsSetting}', [IntegrationSmsController::class, 'show'])->name('sms.show');
        });

        Route::middleware('permission:integrations.api.manage|integrations.manage')->group(function () {
            Route::get('api-keys/create', [IntegrationApiKeyController::class, 'create'])->name('api-keys.create');
            Route::post('api-keys', [IntegrationApiKeyController::class, 'store'])->name('api-keys.store');
        });

        Route::middleware('permission:integrations.view|integrations.api.manage|integrations.manage')->group(function () {
            Route::get('api-keys', [IntegrationApiKeyController::class, 'index'])->name('api-keys.index');
            Route::get('api-keys/export/{format}', [IntegrationApiKeyController::class, 'export'])
                ->where('format', 'csv|excel|pdf')
                ->name('api-keys.export');
            Route::get('api-keys/{apiKey}', [IntegrationApiKeyController::class, 'show'])->name('api-keys.show');
        });

        Route::middleware('permission:integrations.api.manage|integrations.manage')->group(function () {
            Route::post('api-keys/{apiKey}/regenerate', [IntegrationApiKeyController::class, 'regenerate'])->name('api-keys.regenerate');
            Route::post('api-keys/{apiKey}/disable', [IntegrationApiKeyController::class, 'disable'])->name('api-keys.disable');
            Route::post('api-keys/{apiKey}/enable', [IntegrationApiKeyController::class, 'enable'])->name('api-keys.enable');
            Route::delete('api-keys/{apiKey}', [IntegrationApiKeyController::class, 'revoke'])->name('api-keys.revoke');
        });

        Route::middleware('permission:integrations.webhooks.manage|integrations.manage')->group(function () {
            Route::get('webhooks/create', [IntegrationWebhookController::class, 'create'])->name('webhooks.create');
            Route::post('webhooks', [IntegrationWebhookController::class, 'store'])->name('webhooks.store');
        });

        Route::middleware('permission:integrations.view|integrations.webhooks.manage|integrations.manage')->group(function () {
            Route::get('webhooks', [IntegrationWebhookController::class, 'index'])->name('webhooks.index');
        });

        Route::middleware('permission:integrations.webhooks.manage|integrations.manage')->group(function () {
            Route::get('webhooks/{webhook}/edit', [IntegrationWebhookController::class, 'edit'])->name('webhooks.edit');
            Route::put('webhooks/{webhook}', [IntegrationWebhookController::class, 'update'])->name('webhooks.update');
            Route::post('webhooks/{webhook}/test', [IntegrationWebhookController::class, 'test'])->name('webhooks.test');
            Route::post('webhooks/{webhook}/deliveries/{delivery}/retry', [IntegrationWebhookController::class, 'retry'])->name('webhooks.retry');
            Route::post('webhooks/{webhook}/disable', [IntegrationWebhookController::class, 'disable'])->name('webhooks.disable');
            Route::post('webhooks/{webhook}/enable', [IntegrationWebhookController::class, 'enable'])->name('webhooks.enable');
        });

        Route::middleware('permission:integrations.view|integrations.webhooks.manage|integrations.manage')->group(function () {
            Route::get('webhooks/{webhook}', [IntegrationWebhookController::class, 'show'])->name('webhooks.show');
        });

        Route::middleware('permission:integrations.view|integrations.providers.manage|integrations.manage')->group(function () {
            Route::get('providers', [IntegrationProviderController::class, 'index'])->name('providers.index');
            Route::get('providers/{provider}', [IntegrationProviderController::class, 'show'])->name('providers.show');
        });

        Route::middleware('permission:integrations.providers.manage|integrations.manage')->group(function () {
            Route::post('providers/{provider}/connect', [IntegrationProviderController::class, 'connect'])->name('providers.connect');
            Route::post('providers/{provider}/disconnect', [IntegrationProviderController::class, 'disconnect'])->name('providers.disconnect');
            Route::post('providers/{provider}/health-check', [IntegrationProviderController::class, 'healthCheck'])->name('providers.health-check');
            Route::post('providers/{provider}/sync', [IntegrationProviderController::class, 'sync'])->name('providers.sync');
        });
    });

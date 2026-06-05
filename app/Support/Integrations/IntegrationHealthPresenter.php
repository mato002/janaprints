<?php

namespace App\Support\Integrations;

use App\Enums\IntegrationProviderStatus;
use App\Enums\IntegrationWebhookStatus;
use App\Models\Integrations\IntegrationApiKey;
use App\Models\Integrations\IntegrationEmailSetting;
use App\Models\Integrations\IntegrationProvider;
use App\Models\Integrations\IntegrationSmsSetting;
use App\Models\Integrations\IntegrationWebhook;

class IntegrationHealthPresenter
{
    public function build(): array
    {
        $companyId = tenant()->companyId();

        if (! $companyId) {
            return $this->empty();
        }

        $activeEmail = IntegrationEmailSetting::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->first();

        $activeSms = IntegrationSmsSetting::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->first();

        $activeWebhooks = IntegrationWebhook::query()
            ->where('company_id', $companyId)
            ->where('status', IntegrationWebhookStatus::Active)
            ->count();

        $failedWebhooks = IntegrationWebhook::query()
            ->where('company_id', $companyId)
            ->where(fn ($q) => $q->where('last_response_code', '>=', 400)->orWhere('last_response_code', 0))
            ->whereNotNull('last_response_code')
            ->count();

        $failedDeliveries = \App\Models\Integrations\IntegrationWebhookDelivery::query()
            ->whereHas('webhook', fn ($q) => $q->where('company_id', $companyId))
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $connectedProviders = IntegrationProvider::query()
            ->where('company_id', $companyId)
            ->where('status', IntegrationProviderStatus::Connected)
            ->count();

        $apiKeyCount = IntegrationApiKey::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->whereNull('revoked_at')
            ->count();

        return [
            'email' => [
                'status' => $this->emailStatus($activeEmail),
                'label' => $activeEmail?->provider->label() ?? __('Not configured'),
                'route' => 'admin.integrations.email.index',
            ],
            'sms' => [
                'status' => $this->smsStatus($activeSms),
                'label' => $activeSms?->provider->label() ?? __('Not configured'),
                'route' => 'admin.integrations.sms.index',
            ],
            'webhooks' => [
                'active' => $activeWebhooks,
                'failed' => max($failedWebhooks, $failedDeliveries),
                'status' => $failedDeliveries > 0 ? 'yellow' : ($activeWebhooks > 0 ? 'green' : 'yellow'),
                'route' => 'admin.integrations.webhooks.index',
            ],
            'providers' => [
                'connected' => $connectedProviders,
                'status' => $connectedProviders > 0 ? 'green' : 'yellow',
                'route' => 'admin.integrations.providers.index',
            ],
            'api_keys' => [
                'count' => $apiKeyCount,
                'status' => $apiKeyCount > 0 ? 'green' : 'yellow',
                'route' => 'admin.integrations.api-keys.index',
            ],
        ];
    }

    protected function emailStatus(?IntegrationEmailSetting $setting): string
    {
        if ($setting === null) {
            return 'red';
        }

        if ($setting->last_test_success === false) {
            return 'red';
        }

        if ($setting->last_test_success === true || $setting->last_successful_send_at) {
            return 'green';
        }

        return 'yellow';
    }

    protected function smsStatus(?IntegrationSmsSetting $setting): string
    {
        if ($setting === null) {
            return 'red';
        }

        return match ($setting->health_status) {
            'healthy' => 'green',
            'unhealthy' => 'red',
            default => 'yellow',
        };
    }

    protected function empty(): array
    {
        return [
            'email' => ['status' => 'yellow', 'label' => __('Not configured'), 'route' => 'admin.integrations.email.index'],
            'sms' => ['status' => 'yellow', 'label' => __('Not configured'), 'route' => 'admin.integrations.sms.index'],
            'webhooks' => ['active' => 0, 'failed' => 0, 'status' => 'yellow', 'route' => 'admin.integrations.webhooks.index'],
            'providers' => ['connected' => 0, 'status' => 'yellow', 'route' => 'admin.integrations.providers.index'],
            'api_keys' => ['count' => 0, 'status' => 'yellow', 'route' => 'admin.integrations.api-keys.index'],
        ];
    }
}

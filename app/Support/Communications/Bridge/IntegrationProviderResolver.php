<?php

namespace App\Support\Communications\Bridge;

use App\Enums\IntegrationWhatsappProvider;
use App\Enums\WhatsappProvider;
use App\Models\Integrations\IntegrationEmailSetting;
use App\Models\Integrations\IntegrationSmsSetting;
use App\Models\Integrations\IntegrationWhatsappSetting;
use Illuminate\Support\Collection;

class IntegrationProviderResolver
{
    /**
     * @return Collection<int, IntegrationSmsSetting>
     */
    public function smsChain(int $companyId): Collection
    {
        return $this->orderedChain(
            IntegrationSmsSetting::query()->where('company_id', $companyId)->get(),
            fn (IntegrationSmsSetting $setting) => filled($setting->api_url)
                || (filled($setting->username) && filled($setting->api_key)),
        );
    }

    /**
     * @return Collection<int, IntegrationEmailSetting>
     */
    public function emailChain(int $companyId): Collection
    {
        return $this->orderedChain(
            IntegrationEmailSetting::query()->where('company_id', $companyId)->get(),
            fn (IntegrationEmailSetting $setting) => match ($setting->provider->value) {
                'mailgun' => filled($setting->mailgun_api_key) && filled($setting->mailgun_domain),
                'sendgrid' => filled($setting->sendgrid_api_key),
                'ses' => filled($setting->ses_access_key) && filled($setting->ses_secret_key),
                default => filled($setting->smtp_host) && filled($setting->smtp_username),
            },
        );
    }

    /**
     * @return Collection<int, IntegrationWhatsappSetting>
     */
    public function whatsappChain(int $companyId, ?WhatsappProvider $preferred = null): Collection
    {
        $settings = IntegrationWhatsappSetting::query()
            ->where('company_id', $companyId)
            ->get()
            ->filter(fn (IntegrationWhatsappSetting $setting) => $this->whatsappIsConfigured($setting));

        if ($preferred !== null) {
            $mapped = $this->mapWhatsappProvider($preferred);

            if ($mapped !== null) {
                $preferredSettings = $settings->filter(
                    fn (IntegrationWhatsappSetting $setting) => $setting->provider === $mapped,
                );

                if ($preferredSettings->isNotEmpty()) {
                    return $this->orderedChain($preferredSettings);
                }
            }
        }

        return $this->orderedChain($settings);
    }

    /**
     * @template T of IntegrationSmsSetting|IntegrationEmailSetting|IntegrationWhatsappSetting
     *
     * @param  Collection<int, T>  $settings
     * @param  (callable(T): bool)|null  $configured
     * @return Collection<int, T>
     */
    protected function orderedChain(Collection $settings, ?callable $configured = null): Collection
    {
        $items = $configured === null ? $settings : $settings->filter($configured);

        return $items
            ->sortBy([
                fn ($setting) => $setting->is_active ? 0 : 1,
                fn ($setting) => ($setting->health_status ?? 'unknown') === 'healthy' ? 0 : 1,
                fn ($setting) => $setting->id,
            ])
            ->values();
    }

    protected function whatsappIsConfigured(IntegrationWhatsappSetting $setting): bool
    {
        return match ($setting->provider) {
            IntegrationWhatsappProvider::MetaCloud => filled($setting->api_key) && filled($setting->phone_number_id),
            IntegrationWhatsappProvider::Twilio => filled($setting->username) && filled($setting->api_key),
            IntegrationWhatsappProvider::AfricasTalking => filled($setting->api_key) && filled($setting->username),
            IntegrationWhatsappProvider::Http => filled($setting->api_url) && filled($setting->api_key),
        };
    }

    protected function mapWhatsappProvider(WhatsappProvider $provider): ?IntegrationWhatsappProvider
    {
        return match ($provider) {
            WhatsappProvider::MetaCloud => IntegrationWhatsappProvider::MetaCloud,
            WhatsappProvider::Twilio => IntegrationWhatsappProvider::Twilio,
            WhatsappProvider::AfricasTalking => IntegrationWhatsappProvider::AfricasTalking,
            WhatsappProvider::Custom => IntegrationWhatsappProvider::Http,
            WhatsappProvider::Unconfigured => null,
        };
    }
}

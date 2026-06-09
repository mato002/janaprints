<?php

namespace App\Support\Integrations;

use App\Enums\IntegrationEmailProvider;
use App\Models\Integrations\IntegrationEmailSetting;
use Illuminate\Support\Facades\Config;

class IntegrationMailConfigurator
{
    public function mailerName(IntegrationEmailSetting $setting): string
    {
        return 'integration_email_'.$setting->id;
    }

    public function apply(IntegrationEmailSetting $setting, ?string $mailerName = null): string
    {
        $mailer = $mailerName ?? $this->mailerName($setting);
        Config::set('mail.mailers.'.$mailer, $this->configFor($setting));

        return $mailer;
    }

    /**
     * @return array<string, mixed>
     */
    public function configFor(IntegrationEmailSetting $setting): array
    {
        return match ($setting->provider) {
            IntegrationEmailProvider::Mailgun => [
                'transport' => 'smtp',
                'host' => 'smtp.mailgun.org',
                'port' => 587,
                'encryption' => 'tls',
                'username' => $setting->mailgun_domain ? 'postmaster@'.$setting->mailgun_domain : null,
                'password' => $setting->mailgun_api_key,
            ],
            IntegrationEmailProvider::Sendgrid => [
                'transport' => 'smtp',
                'host' => 'smtp.sendgrid.net',
                'port' => 587,
                'encryption' => 'tls',
                'username' => 'apikey',
                'password' => $setting->sendgrid_api_key,
            ],
            IntegrationEmailProvider::Ses => [
                'transport' => 'smtp',
                'host' => 'email-smtp.'.$setting->ses_region.'.amazonaws.com',
                'port' => 587,
                'encryption' => 'tls',
                'username' => $setting->ses_access_key,
                'password' => $setting->ses_secret_key,
            ],
            default => [
                'transport' => 'smtp',
                'host' => $setting->smtp_host,
                'port' => $setting->smtp_port ?? 587,
                'encryption' => $setting->smtp_encryption,
                'username' => $setting->smtp_username,
                'password' => $setting->smtp_password,
            ],
        };
    }
}

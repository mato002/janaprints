<?php

namespace App\Support\Integrations;

use App\Enums\IntegrationEmailProvider;
use App\Models\Integrations\IntegrationEmailSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class EmailSettingsService
{
    public function __construct(
        protected IntegrationAuditService $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function save(IntegrationEmailSetting $setting, array $data, int $userId): IntegrationEmailSetting
    {
        $old = $setting->exists ? $setting->getOriginal() : [];
        $data['updated_by'] = $userId;

        if (empty($data['smtp_password'])) {
            unset($data['smtp_password']);
        }
        if (empty($data['mailgun_api_key'])) {
            unset($data['mailgun_api_key']);
        }
        if (empty($data['sendgrid_api_key'])) {
            unset($data['sendgrid_api_key']);
        }
        if (empty($data['ses_secret_key'])) {
            unset($data['ses_secret_key']);
        }

        $setting->fill($data);
        $setting->save();

        $this->audit->logChange($setting, $setting->wasRecentlyCreated ? 'created' : 'updated', $old, $setting->getAttributes());

        return $setting;
    }

    public function activate(IntegrationEmailSetting $setting, int $userId): void
    {
        IntegrationEmailSetting::query()
            ->where('company_id', $setting->company_id)
            ->where('id', '!=', $setting->id)
            ->update(['is_active' => false]);

        $old = $setting->getOriginal();
        $setting->update(['is_active' => true, 'updated_by' => $userId]);

        $this->audit->logChange($setting, 'provider_activated', $old, $setting->getAttributes());
    }

    public function deactivate(IntegrationEmailSetting $setting, int $userId): void
    {
        $old = $setting->getOriginal();
        $setting->update(['is_active' => false, 'updated_by' => $userId]);
        $this->audit->logChange($setting, 'provider_deactivated', $old, $setting->getAttributes());
    }

    public function testConnection(IntegrationEmailSetting $setting): array
    {
        try {
            $this->applyMailConfig($setting);
            $transport = Mail::mailer('integration_test')->getSymfonyTransport();
            if (method_exists($transport, 'start')) {
                $transport->start();
                if (method_exists($transport, 'stop')) {
                    $transport->stop();
                }
            }

            $setting->update([
                'last_tested_at' => now(),
                'last_test_success' => true,
            ]);

            return ['success' => true, 'message' => __('Connection successful.')];
        } catch (\Throwable $e) {
            $setting->update([
                'last_tested_at' => now(),
                'last_test_success' => false,
                'last_failure_at' => now(),
                'last_failure_message' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function sendTestEmail(IntegrationEmailSetting $setting, string $recipient): array
    {
        try {
            $this->applyMailConfig($setting);

            Mail::mailer('integration_test')->raw(
                __('This is a test email from Jana Prints ERP integration settings.'),
                function ($message) use ($setting, $recipient) {
                    $message->to($recipient)
                        ->subject(__('Jana Prints — Test Email'))
                        ->from($setting->from_email ?? config('mail.from.address'), $setting->from_name ?? config('mail.from.name'));
                },
            );

            $setting->update([
                'last_successful_send_at' => now(),
                'last_tested_at' => now(),
                'last_test_success' => true,
            ]);

            return ['success' => true, 'message' => __('Test email sent.')];
        } catch (\Throwable $e) {
            $setting->update([
                'last_failure_at' => now(),
                'last_failure_message' => $e->getMessage(),
                'last_tested_at' => now(),
                'last_test_success' => false,
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function applyMailConfig(IntegrationEmailSetting $setting): void
    {
        $config = match ($setting->provider) {
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

        Config::set('mail.mailers.integration_test', $config);
    }
}

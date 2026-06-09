<?php

namespace App\Support\Communications\Bridge;

use App\Models\Communications\EmailAccount;
use App\Models\Communications\EmailMessage;
use App\Models\Integrations\IntegrationEmailSetting;
use App\Support\Communications\Email\EmailProviderResult;
use App\Support\Integrations\IntegrationMailConfigurator;
use Illuminate\Support\Facades\Mail;

class IntegrationEmailDriver
{
    public function __construct(
        protected IntegrationMailConfigurator $configurator,
    ) {}

    public function send(
        IntegrationEmailSetting $setting,
        EmailAccount $account,
        EmailMessage $message,
    ): EmailProviderResult {
        $mailer = $this->configurator->apply($setting);
        $to = collect($message->to_emails)->map(fn (array $row) => $row['email'] ?? null)->filter()->values();

        if ($to->isEmpty()) {
            return EmailProviderResult::failed(__('No email recipients configured.'));
        }

        $fromEmail = $setting->from_email ?? $account->from_email;
        $fromName = $setting->from_name ?? $account->from_name;

        try {
            Mail::mailer($mailer)->html($message->body, function ($mail) use ($message, $to, $fromEmail, $fromName, $setting, $account) {
                $mail->to($to->all())
                    ->subject($message->subject)
                    ->from($fromEmail, $fromName);

                $replyTo = $setting->reply_to_email ?? $account->reply_to_email;
                if (filled($replyTo)) {
                    $mail->replyTo($replyTo, $account->reply_to_name);
                }

                foreach ($message->cc_emails ?? [] as $cc) {
                    if (filled($cc['email'] ?? null)) {
                        $mail->cc($cc['email'], $cc['name'] ?? null);
                    }
                }

                foreach ($message->bcc_emails ?? [] as $bcc) {
                    if (filled($bcc['email'] ?? null)) {
                        $mail->bcc($bcc['email'], $bcc['name'] ?? null);
                    }
                }
            });

            $setting->update([
                'last_successful_send_at' => now(),
                'last_tested_at' => now(),
                'last_test_success' => true,
            ]);

            return EmailProviderResult::sent(
                'email-'.$setting->provider->value.'-'.$message->id,
                [
                    'integration_setting_id' => $setting->id,
                    'provider' => $setting->provider->value,
                    'mailer' => $mailer,
                ],
            );
        } catch (\Throwable $e) {
            $setting->update([
                'last_failure_at' => now(),
                'last_failure_message' => $e->getMessage(),
                'last_tested_at' => now(),
                'last_test_success' => false,
            ]);

            return EmailProviderResult::failed($e->getMessage(), [
                'integration_setting_id' => $setting->id,
                'provider' => $setting->provider->value,
            ]);
        }
    }
}

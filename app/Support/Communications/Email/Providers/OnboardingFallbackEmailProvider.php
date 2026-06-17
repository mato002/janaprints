<?php

namespace App\Support\Communications\Email\Providers;

use App\Models\Communications\EmailAccount;
use App\Models\Communications\EmailMessage;
use App\Support\Communications\Email\Contracts\EmailProviderContract;
use App\Support\Communications\Email\EmailProviderResult;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class OnboardingFallbackEmailProvider implements EmailProviderContract
{
    public function send(EmailAccount $account, EmailMessage $message): EmailProviderResult
    {
        $mailer = (string) config('mailboxes.onboarding.mailer', config('mail.auth_mailer', 'onboarding'));
        $to = collect($message->to_emails)->map(fn (array $row) => $row['email'] ?? null)->filter()->values();

        if ($to->isEmpty()) {
            return EmailProviderResult::failed(__('No email recipients configured.'));
        }

        if (! filled(config('mail.mailers.'.$mailer.'.username')) && ! filled(config('mail.mailers.'.$mailer.'.host'))) {
            return EmailProviderResult::failed(__('No active email integration configured.'), [
                'provider' => 'onboarding_fallback',
                'reason' => 'integration_unavailable',
            ]);
        }

        try {
            $message->loadMissing('attachments');

            Mail::mailer($mailer)->html($message->body, function ($mail) use ($message, $to, $account) {
                $mail->to($to->all())
                    ->subject($message->subject)
                    ->from($account->from_email, $account->from_name);

                if (filled($account->reply_to_email)) {
                    $mail->replyTo($account->reply_to_email, $account->reply_to_name);
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

                $disk = (string) config('communications.email_attachment_disk', 'local');

                foreach ($message->attachments as $attachment) {
                    if (! filled($attachment->file_path)) {
                        continue;
                    }

                    if (! Storage::disk($disk)->exists($attachment->file_path)) {
                        continue;
                    }

                    $mail->attach(
                        Storage::disk($disk)->path($attachment->file_path),
                        [
                            'as' => $attachment->label ?? basename($attachment->file_path),
                            'mime' => 'application/pdf',
                        ],
                    );
                }
            });

            return EmailProviderResult::sent(
                'email-onboarding-'.$message->id,
                [
                    'provider' => 'onboarding_fallback',
                    'mailer' => $mailer,
                ],
            );
        } catch (\Throwable $e) {
            return EmailProviderResult::failed($e->getMessage(), [
                'provider' => 'onboarding_fallback',
                'mailer' => $mailer,
            ]);
        }
    }
}

<?php

namespace App\Notifications\Auth;

use App\Mail\Auth\PasswordResetMail;
use App\Services\EmailIdentity\EmailSenderResolver;
use App\Support\Branding\BrandingAssets;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Mail\Mailable;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): Mailable
    {
        $branding = app(BrandingAssets::class);
        $senderResolver = app(EmailSenderResolver::class);
        $companyName = $branding->companyDisplayName();
        $portalLabel = $notifiable->isClientPortalAccount()
            ? __('Client Portal')
            : __('ERP');

        $sender = $senderResolver->resolve('password_reset');
        $fromName = (string) config('mailboxes.auth.from_name', config('app.name'));
        $fromAddress = (string) (
            config('mailboxes.auth.from_address')
            ?: config('mailboxes.onboarding.from_address')
            ?: $sender->address
        );
        $replyToAddress = (string) (
            $senderResolver->resolve('support')->address
            ?: config('mailboxes.auth.reply_to')
            ?: config('mailboxes.onboarding.reply_to')
            ?: $fromAddress
        );
        $mailer = (string) config('mail.auth_mailer', config('mailboxes.auth.mailer', 'onboarding'));

        return (new PasswordResetMail(
            resetUrl: $this->resetUrl($notifiable),
            userName: $notifiable->name,
            portalLabel: $portalLabel,
            companyName: $companyName,
            logoDataUri: $branding->documentsLogoDataUri(),
            expireMinutes: (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60),
            fromAddress: $fromAddress,
            fromName: $fromName,
            replyToAddress: $replyToAddress,
        ))
            ->mailer($mailer)
            ->to($notifiable->getEmailForPasswordReset(), $notifiable->name);
    }

    protected function resetUrl($notifiable): string
    {
        $route = $notifiable->isClientPortalAccount()
            ? 'client.password.reset'
            : 'password.reset';

        return url(route($route, [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}

<?php

namespace App\Notifications\Auth;

use App\Mail\Auth\PasswordResetMail;
use App\Models\Company;
use App\Services\EmailIdentity\EmailSenderResolver;
use App\Support\Branding\BrandingAssets;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Mail\Mailable;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * @return list<string>
     */
    public function via($notifiable)
    {
        return ['corporate-mail'];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function toCorporateMail(object $notifiable): ?array
    {
        $mail = $this->buildPasswordResetMail($notifiable);

        return [
            'company_id' => $this->resolveCompanyId($notifiable),
            'branch_id' => $notifiable->default_branch_id ?? null,
            'user_id' => (int) $notifiable->getKey(),
            'to' => [[
                'email' => $notifiable->getEmailForPasswordReset(),
                'name' => $notifiable->name,
            ]],
            'subject' => $mail->envelope()->subject,
            'body' => $mail->render(),
            'sender_purpose' => 'password_reset',
            'metadata' => [
                'module' => 'security',
                'entity_type' => 'user',
                'entity_id' => (int) $notifiable->getKey(),
                'portal' => $notifiable->isClientPortalAccount() ? 'client' : 'erp',
            ],
        ];
    }

    public function toMail($notifiable): Mailable
    {
        return $this->buildPasswordResetMail($notifiable)
            ->mailer((string) config('mail.auth_mailer', config('mailboxes.auth.mailer', 'onboarding')))
            ->to($notifiable->getEmailForPasswordReset(), $notifiable->name);
    }

    protected function buildPasswordResetMail(object $notifiable): PasswordResetMail
    {
        $branding = app(BrandingAssets::class);
        $senderResolver = app(EmailSenderResolver::class);
        $companyName = $branding->companyDisplayName();
        $portalLabel = $notifiable->isClientPortalAccount()
            ? __('Client Portal')
            : __('ERP');

        $sender = $senderResolver->resolve('password_reset');
        $fromName = (string) config('mailboxes.auth.from_name', config('app.name'));
        $fromAddress = (string) ($sender->address ?: config('mailboxes.auth.from_address'));
        $replyToAddress = (string) (
            $senderResolver->resolve('support')->address
            ?: config('mailboxes.auth.reply_to')
            ?: $fromAddress
        );

        return new PasswordResetMail(
            resetUrl: $this->resetUrl($notifiable),
            userName: $notifiable->name,
            portalLabel: $portalLabel,
            companyName: $companyName,
            logoDataUri: $branding->documentsLogoDataUri(),
            expireMinutes: (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60),
            fromAddress: $fromAddress,
            fromName: $fromName,
            replyToAddress: $replyToAddress,
        );
    }

    protected function resolveCompanyId(object $notifiable): int
    {
        if (filled($notifiable->company_id ?? null)) {
            return (int) $notifiable->company_id;
        }

        return (int) (Company::query()->where('code', config('leads.crm.default_company_code', 'JANA'))->value('id') ?? 1);
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

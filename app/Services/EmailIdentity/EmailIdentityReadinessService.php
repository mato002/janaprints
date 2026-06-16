<?php

namespace App\Services\EmailIdentity;

use App\Models\Integrations\IntegrationSmsSetting;

class EmailIdentityReadinessService
{
    public function __construct(
        protected EmployeeDefaultRoleService $defaultRoles,
        protected EmailSenderResolver $senderResolver,
    ) {}

    /**
     * @return list<array{key: string, label: string, status: string, detail: string}>
     */
    public function checks(?int $companyId = null): array
    {
        $defaultRole = $this->defaultRoles->resolveDefaultRole();
        $hrSender = $this->senderResolver->resolve('employee_onboarding');
        $queueConnection = (string) config('queue.default', 'sync');
        $isProduction = app()->environment('production');
        $localTesting = (bool) config('email_local_testing.enabled', false);
        $localFrom = (string) config('email_local_testing.from_address', '');

        $checks = [];

        if ($localTesting) {
            $checks[] = $this->check(
                'local_testing',
                __('Local email testing mode'),
                filled($localFrom) && filled(config('mail.mailers.onboarding.username')),
                __('Active — onboarding mail sends via :address using :mailer mailer', [
                    'address' => $localFrom ?: '—',
                    'mailer' => (string) config('mailboxes.onboarding.mailer', 'onboarding'),
                ]),
                filled($localFrom) ? 'warning' : 'missing',
            );
        }

        return array_merge($checks, [
            $this->check(
                'mail_from',
                __('MAIL_FROM address'),
                filled(config('mail.from.address')),
                (string) (config('mail.from.address') ?: '—'),
            ),
            $this->check(
                'hr_sender',
                __('HR / onboarding sender'),
                $hrSender->configured,
                $hrSender->configured
                    ? (string) $hrSender->address
                    : __('Using fallback: :address', ['address' => $hrSender->address ?: '—']),
                $hrSender->configured ? 'ready' : 'warning',
            ),
            $this->check(
                'queue_driver',
                __('Queue driver'),
                ! $isProduction || $queueConnection !== 'sync',
                $queueConnection === 'sync' && $isProduction
                    ? __('Sync driver in production — onboarding emails will not queue')
                    : $queueConnection,
                $queueConnection === 'sync' && $isProduction ? 'warning' : 'ready',
            ),
            $this->check(
                'default_role',
                __('Default activation role'),
                $defaultRole !== null,
                $defaultRole
                    ? $defaultRole
                    : __('No fallback role exists — activation may complete without a role'),
                $defaultRole ? 'ready' : 'warning',
            ),
            $this->check(
                'staff_role',
                __('Staff role (preferred default)'),
                $this->defaultRoles->staffRoleExists(),
                $this->defaultRoles->staffRoleExists()
                    ? __('Staff role available')
                    : __('Staff role missing — using Viewer fallback'),
                $this->defaultRoles->staffRoleExists() ? 'ready' : 'warning',
            ),
            $this->check(
                'sms_provider',
                __('SMS provider'),
                $this->smsProviderConfigured($companyId),
                $this->smsProviderConfigured($companyId)
                    ? __('Active SMS integration configured')
                    : __('No active SMS provider — onboarding SMS will be skipped'),
                $this->smsProviderConfigured($companyId) ? 'ready' : 'warning',
            ),
            $this->check(
                'activation_expiry',
                __('Activation expiry'),
                (int) config('mailboxes.activation.token_expiry_hours', 0) > 0,
                __(':hours hours', ['hours' => config('mailboxes.activation.token_expiry_hours', 72)]),
            ),
            $this->check(
                'onboarding_mailer',
                __('Onboarding SMTP mailer'),
                $localTesting
                    ? filled(config('mail.mailers.onboarding.username'))
                    : (filled(config('mail.mailers.onboarding.username')) || config('mail.default') !== 'log'),
                $localTesting
                    ? __('Gmail/dev SMTP (:host)', ['host' => config('mail.mailers.onboarding.host', '—')])
                    : (filled(config('mail.mailers.onboarding.host'))
                        ? (string) config('mail.mailers.onboarding.host')
                        : __('Configure ONBOARDING_MAIL_* variables')),
                ($localTesting && filled(config('mail.mailers.onboarding.username')))
                    || filled(config('mail.mailers.onboarding.username'))
                    ? 'ready'
                    : 'warning',
            ),
            $this->check(
                'cpanel_api',
                __('cPanel API connection'),
                filled(config('mailboxes.cpanel.host'))
                    && filled(config('mailboxes.cpanel.username'))
                    && filled(config('mailboxes.cpanel.api_token'))
                    && filled(config('mailboxes.domain')),
                filled(config('mailboxes.cpanel.api_token'))
                    ? __('API token configured')
                    : __('Configure CPANEL_* and MAILBOX_DOMAIN variables'),
                filled(config('mailboxes.cpanel.api_token')) && filled(config('mailboxes.domain'))
                    ? 'ready'
                    : 'warning',
            ),
        ]);
    }

    /**
     * @return array{overall: string, ready: int, warning: int, missing: int}
     */
    public function summary(?int $companyId = null): array
    {
        $checks = $this->checks($companyId);
        $ready = collect($checks)->where('status', 'ready')->count();
        $warning = collect($checks)->where('status', 'warning')->count();
        $missing = collect($checks)->where('status', 'missing')->count();

        $overall = match (true) {
            $missing > 0 => 'missing',
            $warning > 0 => 'warning',
            default => 'ready',
        };

        return compact('overall', 'ready', 'warning', 'missing');
    }

    /**
     * @return array{key: string, label: string, status: string, detail: string}
     */
    protected function check(string $key, string $label, bool $ok, string $detail, ?string $status = null): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'status' => $status ?? ($ok ? 'ready' : 'missing'),
            'detail' => $detail,
        ];
    }

    protected function smsProviderConfigured(?int $companyId): bool
    {
        if (! $companyId) {
            return false;
        }

        return IntegrationSmsSetting::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->exists();
    }
}

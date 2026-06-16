<?php

namespace App\Services\EmailIdentity;

use App\Enums\EmailIdentity\MailboxAuditAction;
use Illuminate\Support\Str;
use RuntimeException;

class CompanyEmailService
{
    public function __construct(
        protected CpanelApiClient $client,
        protected EmailIdentityAuditService $audit,
    ) {}

    /**
     * @return array{success: bool, message: string, mailbox_count: ?int}
     */
    public function testConnection(): array
    {
        if (! $this->client->isConfigured()) {
            return [
                'success' => false,
                'message' => __('cPanel API credentials are incomplete.'),
                'mailbox_count' => null,
            ];
        }

        try {
            $mailboxes = $this->listMailboxes();

            return [
                'success' => true,
                'message' => __('Connected successfully. :count mailbox(es) found.', ['count' => count($mailboxes)]),
                'mailbox_count' => count($mailboxes),
            ];
        } catch (\Throwable $exception) {
            report($exception);

            return [
                'success' => false,
                'message' => $exception->getMessage(),
                'mailbox_count' => null,
            ];
        }
    }

    /**
     * @return list<array{
     *     email: string,
     *     login: string,
     *     disk_used_mb: ?float,
     *     disk_quota_mb: ?float,
     *     disk_used_percent: ?int,
     *     quota_unlimited: bool,
     *     suspended: bool
     * }>
     */
    public function listMailboxes(): array
    {
        $payload = $this->client->execute('Email', 'list_pops_with_disk');
        $records = $payload['data'] ?? [];

        if (! is_array($records)) {
            return [];
        }

        return collect($records)
            ->filter(fn ($record) => is_array($record) && filled($record['email'] ?? null))
            ->map(fn (array $record) => $this->normalizeMailbox($record))
            ->sortBy('email')
            ->values()
            ->all();
    }

    /**
     * @return array{email: string, quota_mb: int}
     */
    public function createMailbox(string $localPart, string $password, ?int $quotaMb = null): array
    {
        $email = $this->composeAddress($localPart);
        $quota = $quotaMb ?? (int) config('mailboxes.cpanel.default_quota_mb', 250);

        $this->client->execute('Email', 'add_pop', [
            'email' => $email,
            'password' => $password,
            'quota' => max(0, $quota),
        ]);

        $this->audit->log(MailboxAuditAction::CompanyMailboxCreated, metadata: [
            'email' => $email,
            'quota_mb' => $quota,
        ]);

        return [
            'email' => $email,
            'quota_mb' => $quota,
        ];
    }

    public function updatePassword(string $email, string $password): void
    {
        $this->assertMailboxOnDomain($email);

        $this->client->execute('Email', 'passwd_pop', [
            'email' => $email,
            'password' => $password,
        ]);

        $this->audit->log(MailboxAuditAction::CompanyMailboxPasswordUpdated, metadata: [
            'email' => $email,
        ]);
    }

    public function updateQuota(string $email, int $quotaMb): void
    {
        $this->assertMailboxOnDomain($email);

        ['local_part' => $localPart, 'domain' => $domain] = $this->parseEmailAddress($email);

        $this->client->execute('Email', 'edit_pop_quota', [
            'email' => $localPart,
            'domain' => $domain,
            'quota' => max(0, $quotaMb),
        ]);

        $this->audit->log(MailboxAuditAction::CompanyMailboxQuotaUpdated, metadata: [
            'email' => $email,
            'quota_mb' => $quotaMb,
            'quota_unlimited' => $quotaMb === 0,
        ]);
    }

    public function deleteMailbox(string $email): void
    {
        $this->assertMailboxOnDomain($email);

        $this->client->execute('Email', 'delete_pop', [
            'email' => $email,
        ]);

        $this->audit->log(MailboxAuditAction::CompanyMailboxDeleted, metadata: [
            'email' => $email,
        ]);
    }

    public function findMailbox(string $email): ?array
    {
        $this->assertMailboxOnDomain($email);

        return collect($this->listMailboxes())->firstWhere('email', Str::lower($email));
    }

    public function composeAddress(string $localPart): string
    {
        $localPart = Str::lower(trim($localPart));
        $domain = Str::lower((string) config('mailboxes.domain'));

        if ($localPart === '' || $domain === '') {
            throw new RuntimeException(__('Mailbox domain is not configured.'));
        }

        if (! preg_match('/^[a-z0-9][a-z0-9._-]*$/', $localPart)) {
            throw new RuntimeException(__('The mailbox name contains invalid characters.'));
        }

        return "{$localPart}@{$domain}";
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array{
     *     email: string,
     *     login: string,
     *     disk_used_mb: ?float,
     *     disk_quota_mb: ?float,
     *     disk_used_percent: ?int,
     *     quota_unlimited: bool,
     *     suspended: bool
     * }
     */
    protected function normalizeMailbox(array $record): array
    {
        $diskUsed = $this->resolveDiskUsedMegabytes($record);
        $quotaUnlimited = $this->isUnlimitedQuota($record);
        $diskQuota = $quotaUnlimited ? null : $this->resolveDiskQuotaMegabytes($record);

        $diskUsedPercent = null;
        if ($diskUsed !== null && $diskQuota !== null && $diskQuota > 0) {
            $diskUsedPercent = (int) min(100, round(($diskUsed / $diskQuota) * 100));
        } elseif (isset($record['diskusedpercent_float']) && is_numeric($record['diskusedpercent_float'])) {
            $diskUsedPercent = (int) min(100, round((float) $record['diskusedpercent_float']));
        } elseif (isset($record['diskusedpercent']) && is_numeric($record['diskusedpercent'])) {
            $diskUsedPercent = (int) min(100, round((float) $record['diskusedpercent']));
        }

        return [
            'email' => Str::lower((string) $record['email']),
            'login' => (string) ($record['login'] ?? $record['email']),
            'disk_used_mb' => $diskUsed,
            'disk_quota_mb' => $diskQuota,
            'disk_used_percent' => $diskUsedPercent,
            'quota_unlimited' => $quotaUnlimited,
            'suspended' => filter_var($record['suspended_login'] ?? false, FILTER_VALIDATE_BOOL),
        ];
    }

    protected function resolveDiskUsedMegabytes(array $record): ?float
    {
        if (isset($record['diskused']) && is_numeric($record['diskused'])) {
            return round((float) $record['diskused'], 2);
        }

        return $this->bytesToMegabytes($record['_diskused'] ?? null);
    }

    protected function resolveDiskQuotaMegabytes(array $record): ?float
    {
        foreach (['diskquota', 'txtdiskquota'] as $key) {
            $value = $record[$key] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            if (is_string($value) && in_array(strtolower($value), ['unlimited', 'none', '∞'], true)) {
                return null;
            }

            if (is_numeric($value)) {
                return round((float) $value, 2);
            }
        }

        return $this->bytesToMegabytes($record['_diskquota'] ?? null);
    }

    protected function isUnlimitedQuota(array $record): bool
    {
        foreach (['diskquota', 'txtdiskquota'] as $key) {
            $value = strtolower(trim((string) ($record[$key] ?? '')));

            if ($value !== '' && in_array($value, ['unlimited', 'none', '∞'], true)) {
                return true;
            }
        }

        $humanQuota = strtolower(trim((string) ($record['humandiskquota'] ?? '')));

        if ($humanQuota !== '' && in_array($humanQuota, ['none', 'unlimited', '∞'], true)) {
            return true;
        }

        if (isset($record['_diskquota']) && (int) $record['_diskquota'] === 0) {
            return true;
        }

        return false;
    }

    protected function bytesToMegabytes(mixed $value): ?float
    {
        if (! is_numeric($value)) {
            return null;
        }

        return round(((float) $value) / 1024 / 1024, 2);
    }

    protected function assertMailboxOnDomain(string $email): void
    {
        $domain = Str::lower((string) config('mailboxes.domain'));
        $normalized = Str::lower(trim($email));

        if ($domain === '' || ! Str::endsWith($normalized, '@'.$domain)) {
            throw new RuntimeException(__('This mailbox does not belong to the configured company domain.'));
        }
    }

    /**
     * @return array{local_part: string, domain: string}
     */
    protected function parseEmailAddress(string $email): array
    {
        $this->assertMailboxOnDomain($email);

        $normalized = Str::lower(trim($email));
        [$localPart, $domain] = explode('@', $normalized, 2);

        if ($localPart === '' || $domain === '') {
            throw new RuntimeException(__('Invalid mailbox address.'));
        }

        return [
            'local_part' => $localPart,
            'domain' => $domain,
        ];
    }
}

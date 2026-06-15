<?php

namespace App\Services\EmailIdentity;

use App\DataTransferObjects\EmailIdentity\CpanelMailboxResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CpanelMailboxGateway
{
    public function isConfigured(): bool
    {
        return filled(config('mailboxes.cpanel.host'))
            && filled(config('mailboxes.cpanel.username'))
            && filled(config('mailboxes.cpanel.api_token'));
    }

    public function isMockMode(): bool
    {
        return ! $this->isConfigured();
    }

    /**
     * Safe read-only connectivity check — does not create or modify mailboxes.
     */
    public function testConnection(): CpanelMailboxResult
    {
        if (! $this->isConfigured()) {
            return new CpanelMailboxResult(
                success: false,
                error: __('cPanel credentials are not configured.'),
                metadata: ['mocked' => true, 'action' => 'test_connection'],
            );
        }

        $domain = (string) config('mailboxes.domain');

        return $this->execute('Email', 'list_pops', [
            'domain' => $domain,
        ], 'test_connection', $domain);
    }

    public function createMailbox(string $email, string $password, ?int $quotaMb = null): CpanelMailboxResult
    {
        [$localPart, $domain] = $this->splitEmail($email);

        if (! $this->isConfigured()) {
            return new CpanelMailboxResult(
                success: true,
                metadata: [
                    'mocked' => true,
                    'action' => 'create',
                    'email' => $email,
                ],
            );
        }

        return $this->execute('Email', 'add_pop', [
            'email' => $localPart,
            'password' => $password,
            'domain' => $domain,
            'quota' => $quotaMb ?? config('mailboxes.cpanel.default_quota_mb', 250),
        ], 'create', $email);
    }

    public function suspendMailbox(string $email): CpanelMailboxResult
    {
        [$localPart, $domain] = $this->splitEmail($email);

        if (! $this->isConfigured()) {
            return new CpanelMailboxResult(
                success: true,
                metadata: ['mocked' => true, 'action' => 'suspend', 'email' => $email],
            );
        }

        return $this->execute('Email', 'suspend_login', [
            'email' => $localPart,
            'domain' => $domain,
        ], 'suspend', $email);
    }

    public function deleteMailbox(string $email): CpanelMailboxResult
    {
        [$localPart, $domain] = $this->splitEmail($email);

        if (! $this->isConfigured()) {
            return new CpanelMailboxResult(
                success: true,
                metadata: ['mocked' => true, 'action' => 'delete', 'email' => $email],
            );
        }

        return $this->execute('Email', 'delete_pop', [
            'email' => $localPart,
            'domain' => $domain,
        ], 'delete', $email);
    }

    public function mailboxExists(string $email): bool
    {
        $status = $this->getMailboxStatus($email);

        return $status['exists'] ?? false;
    }

    /**
     * @return array{exists: bool, suspended: bool|null, raw: array<string, mixed>|null}
     */
    public function getMailboxStatus(string $email): array
    {
        [$localPart, $domain] = $this->splitEmail($email);

        if (! $this->isConfigured()) {
            return ['exists' => false, 'suspended' => null, 'raw' => null];
        }

        $result = $this->execute('Email', 'list_pops', [
            'domain' => $domain,
        ], 'status', $email);

        if (! $result->success) {
            return ['exists' => false, 'suspended' => null, 'raw' => $result->metadata];
        }

        $accounts = collect($result->metadata['response']['data'] ?? [])
            ->filter(fn ($account) => is_array($account))
            ->values();

        $match = $accounts->first(function (array $account) use ($localPart, $email) {
            $login = Str::lower((string) ($account['login'] ?? $account['email'] ?? ''));

            return $login === Str::lower($email) || $login === Str::lower($localPart);
        });

        if (! is_array($match)) {
            return ['exists' => false, 'suspended' => null, 'raw' => $result->metadata];
        }

        return [
            'exists' => true,
            'suspended' => (bool) ($match['suspended_login'] ?? $match['suspended'] ?? false),
            'raw' => $match,
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     */
    protected function execute(string $module, string $function, array $params, string $action, string $email): CpanelMailboxResult
    {
        $host = $this->normalizedHost();
        $port = config('mailboxes.cpanel.port', 2083);
        $username = config('mailboxes.cpanel.username');
        $token = config('mailboxes.cpanel.api_token');

        $url = sprintf('https://%s:%d/execute/%s/%s', $host, $port, $module, $function);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'cpanel '.$username.':'.$token,
            ])
                ->timeout(30)
                ->asForm()
                ->post($url, $params);

            $body = $response->json();

            if ($response->successful() && ($body['status'] ?? 0) === 1) {
                return new CpanelMailboxResult(
                    success: true,
                    metadata: [
                        'action' => $action,
                        'email' => $email,
                        'response' => $body,
                    ],
                );
            }

            $errors = $body['errors'] ?? [$response->body()];

            return new CpanelMailboxResult(
                success: false,
                error: is_array($errors) ? implode('; ', array_map('strval', $errors)) : (string) $errors,
                metadata: [
                    'action' => $action,
                    'email' => $email,
                    'response' => $body,
                    'http_status' => $response->status(),
                ],
            );
        } catch (\Throwable $exception) {
            return new CpanelMailboxResult(
                success: false,
                error: $exception->getMessage(),
                metadata: [
                    'action' => $action,
                    'email' => $email,
                ],
            );
        }
    }

    protected function normalizedHost(): string
    {
        $host = rtrim((string) config('mailboxes.cpanel.host'), '/');

        return preg_replace('#^https?://#i', '', $host) ?: $host;
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function splitEmail(string $email): array
    {
        [$localPart, $domain] = explode('@', Str::lower(trim($email)), 2);

        return [$localPart, $domain];
    }
}

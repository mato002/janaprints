<?php

namespace App\Services\EmailIdentity;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CpanelApiClient
{
    /**
     * @param  array<string, scalar|null>  $params
     * @return array<string, mixed>
     */
    public function execute(string $module, string $function, array $params = []): array
    {
        $this->ensureConfigured();

        $response = Http::timeout(30)
            ->withOptions(['verify' => (bool) config('mailboxes.cpanel.verify_ssl', true)])
            ->withHeaders([
                'Authorization' => 'cpanel '.config('mailboxes.cpanel.username').':'.config('mailboxes.cpanel.api_token'),
            ])
            ->get($this->endpoint($module, $function), $params);

        return $this->parseResponse($response, "{$module}::{$function}");
    }

    public function isConfigured(): bool
    {
        return filled(config('mailboxes.cpanel.host'))
            && filled(config('mailboxes.cpanel.username'))
            && filled(config('mailboxes.cpanel.api_token'))
            && filled(config('mailboxes.domain'));
    }

    /**
     * @return array{configured: bool, host: ?string, domain: ?string, username: ?string, token_configured: bool}
     */
    public function connectionSummary(): array
    {
        return [
            'configured' => $this->isConfigured(),
            'host' => filled(config('mailboxes.cpanel.host')) ? (string) config('mailboxes.cpanel.host') : null,
            'domain' => filled(config('mailboxes.domain')) ? (string) config('mailboxes.domain') : null,
            'username' => filled(config('mailboxes.cpanel.username')) ? (string) config('mailboxes.cpanel.username') : null,
            'token_configured' => filled(config('mailboxes.cpanel.api_token')),
        ];
    }

    protected function ensureConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException(__('cPanel API is not configured. Set CPANEL_* and MAILBOX_DOMAIN environment variables.'));
        }
    }

    protected function endpoint(string $module, string $function): string
    {
        $host = (string) config('mailboxes.cpanel.host');
        $port = (int) config('mailboxes.cpanel.port', 2083);

        return "https://{$host}:{$port}/execute/{$module}/{$function}";
    }

    /**
     * @return array<string, mixed>
     */
    protected function parseResponse(Response $response, string $operation): array
    {
        if (! $response->successful()) {
            throw new RuntimeException(__(
                'cPanel request failed (:operation): HTTP :status',
                ['operation' => $operation, 'status' => $response->status()],
            ));
        }

        /** @var array<string, mixed>|null $payload */
        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException(__('cPanel returned an invalid response for :operation.', ['operation' => $operation]));
        }

        if (($payload['status'] ?? 0) != 1) {
            $errors = collect($payload['errors'] ?? [])
                ->filter(fn ($error) => filled($error))
                ->implode(' ');

            throw new RuntimeException($errors !== ''
                ? $errors
                : __('cPanel rejected the :operation request.', ['operation' => $operation]));
        }

        return $payload;
    }
}

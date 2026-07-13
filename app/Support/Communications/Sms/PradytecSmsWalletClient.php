<?php

namespace App\Support\Communications\Sms;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Pradytec CRM SMS wallet API client.
 *
 * @see https://crm.pradytecai.com/api-documentation
 */
class PradytecSmsWalletClient
{
    public function configured(): bool
    {
        return filled(config('pradytec_sms.api_key'))
            && filled(config('pradytec_sms.client_id'))
            && filled(config('pradytec_sms.api_url'));
    }

    /**
     * @return array{ok: bool, error?: string, balance?: float, currency?: string, units?: float, price_per_unit?: float, low_balance?: bool}
     */
    public function balance(): array
    {
        if (! $this->configured()) {
            return ['ok' => false, 'error' => __('Pradytec SMS wallet is not configured.')];
        }

        $response = $this->request('get', $this->url((string) config('pradytec_sms.balance_path')));

        if (! ($response['ok'] ?? false)) {
            return ['ok' => false, 'error' => (string) ($response['error'] ?? __('Could not load CRM SMS balance.'))];
        }

        $json = $response['json'] ?? [];

        return [
            'ok' => true,
            'balance' => (float) ($json['balance'] ?? $json['units'] ?? 0),
            'currency' => (string) ($json['currency'] ?? config('pradytec_sms.currency')),
            'units' => (float) ($json['units'] ?? $json['balance'] ?? 0),
            'price_per_unit' => (float) ($json['price_per_unit'] ?? 1),
            'low_balance' => (bool) ($json['low_balance'] ?? false),
        ];
    }

    /**
     * @return array{ok: bool, error?: string, status?: string, message?: string, transaction_id?: string, checkout_request_id?: string, amount?: float, phone_number?: string}
     */
    public function initiateTopup(float $amount, string $phoneNumber): array
    {
        if (! $this->configured()) {
            return ['ok' => false, 'error' => __('Pradytec SMS wallet is not configured. Set PRADYTEC_SMS_API_KEY and PRADYTEC_SMS_CLIENT_ID.')];
        }

        $min = (float) config('pradytec_sms.min_topup_amount', 10);
        $max = (float) config('pradytec_sms.max_topup_amount', 50000);
        $amount = round($amount, 2);

        if ($amount < $min) {
            return ['ok' => false, 'error' => __('Minimum top-up is :amount :currency.', [
                'amount' => number_format($min, 0),
                'currency' => config('pradytec_sms.currency'),
            ])];
        }

        if ($amount > $max) {
            return ['ok' => false, 'error' => __('Maximum top-up is :amount :currency.', [
                'amount' => number_format($max, 0),
                'currency' => config('pradytec_sms.currency'),
            ])];
        }

        $phone = $this->normalizeMpesaPhone($phoneNumber);

        if ($phone === null) {
            return ['ok' => false, 'error' => __('Enter a valid Safaricom number (e.g. 07XXXXXXXX).')];
        }

        $response = $this->request('post', $this->url((string) config('pradytec_sms.topup_path')), [
            'amount' => (int) ceil($amount),
            'payment_method' => 'mpesa',
            'phone_number' => $phone,
        ]);

        if (! ($response['ok'] ?? false)) {
            return ['ok' => false, 'error' => (string) ($response['error'] ?? __('Could not initiate M-Pesa top-up.'))];
        }

        $json = $response['json'] ?? [];
        $status = Str::lower((string) ($json['status'] ?? ''));

        if (! in_array($status, ['pending', 'success', 'completed', 'processing'], true)) {
            return ['ok' => false, 'error' => (string) ($json['message'] ?? __('Provider rejected the top-up request.'))];
        }

        return [
            'ok' => true,
            'status' => $status,
            'message' => (string) ($json['message'] ?? __('Please check your phone for the M-Pesa prompt.')),
            'transaction_id' => (string) ($json['transaction_id'] ?? ''),
            'checkout_request_id' => (string) ($json['checkout_request_id'] ?? ''),
            'amount' => (float) ($json['amount'] ?? $amount),
            'phone_number' => $phone,
        ];
    }

    /**
     * @return array{ok: bool, error?: string, transaction?: array<string, mixed>}
     */
    public function topupStatus(string $transactionId): array
    {
        $transactionId = trim($transactionId);

        if ($transactionId === '') {
            return ['ok' => false, 'error' => __('Missing transaction id.')];
        }

        if (! $this->configured()) {
            return ['ok' => false, 'error' => __('Pradytec SMS wallet is not configured.')];
        }

        $path = trim((string) config('pradytec_sms.topup_path'), '/').'/'.rawurlencode($transactionId);
        $response = $this->request('get', $this->url($path));

        if (! ($response['ok'] ?? false)) {
            return ['ok' => false, 'error' => (string) ($response['error'] ?? __('Could not load top-up status.'))];
        }

        return [
            'ok' => true,
            'transaction' => is_array($response['json'] ?? null) ? $response['json'] : [],
        ];
    }

    public function normalizeMpesaPhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            $digits = '254'.substr($digits, 1);
        }

        if (str_starts_with($digits, '7') && strlen($digits) === 9) {
            $digits = '254'.$digits;
        }

        if (str_starts_with($digits, '254') && strlen($digits) === 12 && preg_match('/^254[17]\d{8}$/', $digits)) {
            return $digits;
        }

        return null;
    }

    protected function url(string $path): string
    {
        $base = rtrim((string) config('pradytec_sms.api_url'), '/');
        $clientId = trim((string) config('pradytec_sms.client_id'), '/');
        $path = ltrim($path, '/');

        return "{$base}/{$clientId}/{$path}";
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{ok: bool, json?: array<string, mixed>|null, error?: string, status?: int}
     */
    protected function request(string $method, string $url, array $payload = []): array
    {
        try {
            /** @var PendingRequest $http */
            $http = Http::timeout((int) config('pradytec_sms.timeout_seconds', 25))
                ->withHeaders([
                    'X-API-KEY' => (string) config('pradytec_sms.api_key'),
                    'Accept' => 'application/json',
                ])
                ->acceptJson();

            if (! config('pradytec_sms.verify_ssl', true)) {
                $http = $http->withoutVerifying();
            }

            $response = strtolower($method) === 'get'
                ? $http->get($url)
                : $http->asJson()->post($url, $payload);

            $json = $response->json();
            $json = is_array($json) ? $json : null;

            if (! $response->successful()) {
                $message = is_array($json)
                    ? (string) ($json['message'] ?? $json['error'] ?? $response->body())
                    : $response->body();

                return [
                    'ok' => false,
                    'status' => $response->status(),
                    'json' => $json,
                    'error' => $message !== '' ? $message : __('CRM wallet request failed.'),
                ];
            }

            return [
                'ok' => true,
                'status' => $response->status(),
                'json' => $json,
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}

<?php

namespace App\Support\Communications\Bridge;

use App\Enums\IntegrationSmsProvider;
use App\Models\Communications\SmsMessage;
use App\Models\Integrations\IntegrationSmsSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class IntegrationSmsDriver
{
    public function send(IntegrationSmsSetting $setting, SmsMessage $message): ProviderSendResult
    {
        $requestPayload = [
            'to' => $message->phone_number,
            'body' => $message->message_body,
            'segments' => $message->segments_count,
            'sender_id' => $setting->sender_id,
        ];

        return match ($setting->provider) {
            IntegrationSmsProvider::Twilio => $this->sendTwilio($setting, $message, $requestPayload),
            IntegrationSmsProvider::AfricasTalking => $this->sendAfricasTalking($setting, $message, $requestPayload),
            IntegrationSmsProvider::Onfon, IntegrationSmsProvider::Http => $this->sendHttp($setting, $message, $requestPayload),
        };
    }

    /**
     * @param  array<string, mixed>  $requestPayload
     */
    protected function sendTwilio(
        IntegrationSmsSetting $setting,
        SmsMessage $message,
        array $requestPayload,
    ): ProviderSendResult {
        $accountSid = (string) $setting->username;
        $url = filled($setting->api_url)
            ? (string) $setting->api_url
            : "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";

        try {
            $response = Http::timeout(20)
                ->withBasicAuth($accountSid, (string) $setting->api_key)
                ->asForm()
                ->post($url, [
                    'To' => $message->phone_number,
                    'From' => $setting->sender_id,
                    'Body' => $message->message_body,
                ]);

            $body = $response->json() ?? ['body' => $response->body()];

            if ($response->successful()) {
                return ProviderSendResult::success(
                    $response->status(),
                    (string) ($body['sid'] ?? null),
                    $body,
                    $requestPayload,
                );
            }

            return ProviderSendResult::failure(
                $response->status(),
                (string) ($body['message'] ?? $response->body()),
                $body,
                $requestPayload,
            );
        } catch (\Throwable $e) {
            return ProviderSendResult::failure(0, $e->getMessage(), requestPayload: $requestPayload);
        }
    }

    /**
     * @param  array<string, mixed>  $requestPayload
     */
    protected function sendAfricasTalking(
        IntegrationSmsSetting $setting,
        SmsMessage $message,
        array $requestPayload,
    ): ProviderSendResult {
        $url = filled($setting->api_url)
            ? (string) $setting->api_url
            : 'https://api.africastalking.com/version1/messaging';

        try {
            $response = Http::timeout(20)
                ->withHeaders([
                    'apiKey' => (string) $setting->api_key,
                    'Accept' => 'application/json',
                ])
                ->asForm()
                ->post($url, [
                    'username' => (string) $setting->username,
                    'to' => $message->phone_number,
                    'message' => $message->message_body,
                    'from' => $setting->sender_id,
                ]);

            $body = $response->json() ?? ['body' => $response->body()];

            if ($response->successful()) {
                $messageId = data_get($body, 'SMSMessageData.Recipients.0.messageId')
                    ?? data_get($body, 'SMSMessageData.Recipients.0.message_id');

                return ProviderSendResult::success(
                    $response->status(),
                    $messageId ? (string) $messageId : 'AT-'.Str::upper(Str::random(10)),
                    is_array($body) ? $body : ['body' => $body],
                    $requestPayload,
                );
            }

            return ProviderSendResult::failure(
                $response->status(),
                (string) ($body['errorMessage'] ?? $response->body()),
                is_array($body) ? $body : ['body' => $body],
                $requestPayload,
            );
        } catch (\Throwable $e) {
            return ProviderSendResult::failure(0, $e->getMessage(), requestPayload: $requestPayload);
        }
    }

    /**
     * @param  array<string, mixed>  $requestPayload
     */
    protected function sendHttp(
        IntegrationSmsSetting $setting,
        SmsMessage $message,
        array $requestPayload,
    ): ProviderSendResult {
        if (! filled($setting->api_url)) {
            return ProviderSendResult::failure(422, __('API URL is required.'), requestPayload: $requestPayload);
        }

        try {
            $response = Http::timeout(20)
                ->withHeaders($this->authHeaders($setting))
                ->post((string) $setting->api_url, [
                    'to' => $message->phone_number,
                    'message' => $message->message_body,
                    'sender_id' => $setting->sender_id,
                ]);

            $body = $response->json() ?? ['body' => $response->body()];

            if ($response->successful()) {
                return ProviderSendResult::success(
                    $response->status(),
                    (string) ($body['message_id'] ?? $body['id'] ?? 'HTTP-'.Str::upper(Str::random(10))),
                    is_array($body) ? $body : ['body' => $body],
                    $requestPayload,
                );
            }

            return ProviderSendResult::failure(
                $response->status(),
                (string) ($body['message'] ?? $response->body()),
                is_array($body) ? $body : ['body' => $body],
                $requestPayload,
            );
        } catch (\Throwable $e) {
            return ProviderSendResult::failure(0, $e->getMessage(), requestPayload: $requestPayload);
        }
    }

    /**
     * @return array<string, string>
     */
    protected function authHeaders(IntegrationSmsSetting $setting): array
    {
        $headers = ['Accept' => 'application/json'];

        if (filled($setting->api_key)) {
            $headers['Authorization'] = 'Bearer '.$setting->api_key;
        }

        return $headers;
    }
}

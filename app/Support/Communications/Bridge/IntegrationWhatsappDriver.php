<?php

namespace App\Support\Communications\Bridge;

use App\Enums\IntegrationWhatsappProvider;
use App\Enums\WhatsappDeliveryStatus;
use App\Models\Communications\WhatsappMessage;
use App\Models\Integrations\IntegrationWhatsappSetting;
use App\Support\Communications\Whatsapp\WhatsappProviderResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class IntegrationWhatsappDriver
{
    public function send(IntegrationWhatsappSetting $setting, WhatsappMessage $message): WhatsappProviderResult
    {
        $message->loadMissing('conversation');
        $phone = (string) ($message->conversation?->phone_number ?? '');

        if ($phone === '') {
            return WhatsappProviderResult::failed(__('WhatsApp recipient phone number is missing.'));
        }

        $requestPayload = [
            'to' => $phone,
            'body' => $message->body,
            'message_type' => $message->message_type->value,
        ];

        $result = match ($setting->provider) {
            IntegrationWhatsappProvider::MetaCloud => $this->sendMetaCloud($setting, $phone, $message, $requestPayload),
            IntegrationWhatsappProvider::Twilio => $this->sendTwilio($setting, $phone, $message, $requestPayload),
            IntegrationWhatsappProvider::AfricasTalking => $this->sendAfricasTalking($setting, $phone, $message, $requestPayload),
            IntegrationWhatsappProvider::Http => $this->sendHttp($setting, $phone, $message, $requestPayload),
        };

        if ($result->status === WhatsappDeliveryStatus::Failed) {
            $setting->increment('failed_count');
            $setting->update(['health_status' => 'unhealthy', 'last_health_check_at' => now()]);
        } else {
            $setting->increment('messages_sent_today');
            $setting->increment('messages_sent_month');
            $setting->update(['health_status' => 'healthy', 'last_health_check_at' => now()]);
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $requestPayload
     */
    protected function sendMetaCloud(
        IntegrationWhatsappSetting $setting,
        string $phone,
        WhatsappMessage $message,
        array $requestPayload,
    ): WhatsappProviderResult {
        $phoneNumberId = (string) $setting->phone_number_id;
        $url = filled($setting->api_url)
            ? (string) $setting->api_url
            : "https://graph.facebook.com/v19.0/{$phoneNumberId}/messages";

        try {
            $response = Http::timeout(20)
                ->withToken((string) $setting->api_key)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'to' => ltrim($phone, '+'),
                    'type' => 'text',
                    'text' => ['body' => $message->body],
                ]);

            $body = $response->json() ?? ['body' => $response->body()];

            if ($response->successful()) {
                return WhatsappProviderResult::sent(
                    (string) data_get($body, 'messages.0.id', 'WA-'.Str::upper(Str::random(10))),
                    [
                        'integration_setting_id' => $setting->id,
                        'provider' => $setting->provider->value,
                        'response' => $body,
                        'request' => $requestPayload,
                    ],
                );
            }

            return WhatsappProviderResult::failed(
                (string) data_get($body, 'error.message', $response->body()),
                [
                    'integration_setting_id' => $setting->id,
                    'provider' => $setting->provider->value,
                    'response' => $body,
                    'request' => $requestPayload,
                ],
            );
        } catch (\Throwable $e) {
            return WhatsappProviderResult::failed($e->getMessage(), ['request' => $requestPayload]);
        }
    }

    /**
     * @param  array<string, mixed>  $requestPayload
     */
    protected function sendTwilio(
        IntegrationWhatsappSetting $setting,
        string $phone,
        WhatsappMessage $message,
        array $requestPayload,
    ): WhatsappProviderResult {
        $accountSid = (string) $setting->username;
        $url = filled($setting->api_url)
            ? (string) $setting->api_url
            : "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";

        try {
            $response = Http::timeout(20)
                ->withBasicAuth($accountSid, (string) $setting->api_key)
                ->asForm()
                ->post($url, [
                    'To' => 'whatsapp:'.$phone,
                    'From' => 'whatsapp:'.($setting->sender_phone ?? ''),
                    'Body' => $message->body,
                ]);

            $body = $response->json() ?? ['body' => $response->body()];

            if ($response->successful()) {
                return WhatsappProviderResult::sent(
                    (string) ($body['sid'] ?? 'TW-'.Str::upper(Str::random(10))),
                    [
                        'integration_setting_id' => $setting->id,
                        'provider' => $setting->provider->value,
                        'response' => $body,
                        'request' => $requestPayload,
                    ],
                );
            }

            return WhatsappProviderResult::failed(
                (string) ($body['message'] ?? $response->body()),
                [
                    'integration_setting_id' => $setting->id,
                    'provider' => $setting->provider->value,
                    'response' => $body,
                    'request' => $requestPayload,
                ],
            );
        } catch (\Throwable $e) {
            return WhatsappProviderResult::failed($e->getMessage(), ['request' => $requestPayload]);
        }
    }

    /**
     * @param  array<string, mixed>  $requestPayload
     */
    protected function sendAfricasTalking(
        IntegrationWhatsappSetting $setting,
        string $phone,
        WhatsappMessage $message,
        array $requestPayload,
    ): WhatsappProviderResult {
        $url = filled($setting->api_url)
            ? (string) $setting->api_url
            : 'https://api.africastalking.com/version1/whatsapp/message';

        try {
            $response = Http::timeout(20)
                ->withHeaders([
                    'apiKey' => (string) $setting->api_key,
                    'Accept' => 'application/json',
                ])
                ->post($url, [
                    'username' => (string) $setting->username,
                    'to' => $phone,
                    'message' => $message->body,
                ]);

            $body = $response->json() ?? ['body' => $response->body()];

            if ($response->successful()) {
                return WhatsappProviderResult::sent(
                    (string) data_get($body, 'id', 'AT-WA-'.Str::upper(Str::random(10))),
                    [
                        'integration_setting_id' => $setting->id,
                        'provider' => $setting->provider->value,
                        'response' => $body,
                        'request' => $requestPayload,
                    ],
                );
            }

            return WhatsappProviderResult::failed(
                (string) data_get($body, 'errorMessage', $response->body()),
                [
                    'integration_setting_id' => $setting->id,
                    'provider' => $setting->provider->value,
                    'response' => $body,
                    'request' => $requestPayload,
                ],
            );
        } catch (\Throwable $e) {
            return WhatsappProviderResult::failed($e->getMessage(), ['request' => $requestPayload]);
        }
    }

    /**
     * @param  array<string, mixed>  $requestPayload
     */
    protected function sendHttp(
        IntegrationWhatsappSetting $setting,
        string $phone,
        WhatsappMessage $message,
        array $requestPayload,
    ): WhatsappProviderResult {
        if (! filled($setting->api_url)) {
            return WhatsappProviderResult::failed(__('API URL is required.'));
        }

        try {
            $response = Http::timeout(20)
                ->withHeaders(['Authorization' => 'Bearer '.$setting->api_key, 'Accept' => 'application/json'])
                ->post((string) $setting->api_url, [
                    'to' => $phone,
                    'message' => $message->body,
                    'sender_phone' => $setting->sender_phone,
                ]);

            $body = $response->json() ?? ['body' => $response->body()];

            if ($response->successful()) {
                return WhatsappProviderResult::sent(
                    (string) ($body['message_id'] ?? $body['id'] ?? 'HTTP-WA-'.Str::upper(Str::random(10))),
                    [
                        'integration_setting_id' => $setting->id,
                        'provider' => $setting->provider->value,
                        'response' => $body,
                        'request' => $requestPayload,
                    ],
                );
            }

            return WhatsappProviderResult::failed(
                (string) ($body['message'] ?? $response->body()),
                [
                    'integration_setting_id' => $setting->id,
                    'provider' => $setting->provider->value,
                    'response' => $body,
                    'request' => $requestPayload,
                ],
            );
        } catch (\Throwable $e) {
            return WhatsappProviderResult::failed($e->getMessage(), ['request' => $requestPayload]);
        }
    }
}

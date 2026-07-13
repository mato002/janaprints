<?php

namespace App\Support\Communications\Sms;

use App\Enums\SmsDeliveryStatus;
use App\Models\Communications\SmsMessage;
use App\Models\Communications\SmsProviderLog;
use App\Support\Communications\Bridge\IntegrationProviderResolver;
use App\Support\Communications\Bridge\IntegrationSmsDriver;
use Illuminate\Support\Str;

/**
 * Routes SMS through Administration → Integrations settings with failover.
 * Falls back to an internal stub only when no SMS integration is configured.
 */
class SmsProviderGateway
{
    public function __construct(
        protected IntegrationProviderResolver $resolver,
        protected IntegrationSmsDriver $driver,
    ) {}

    /**
     * @return array{success: bool, delivery_status: SmsDeliveryStatus, provider_message_id: ?string, response: array<string, mixed>}
     */
    public function send(SmsMessage $message): array
    {
        $chain = $this->resolver->smsChain((int) $message->company_id);

        if ($chain->isEmpty()) {
            return $this->sendStub($message);
        }

        $attempt = 0;
        $lastFailure = null;

        foreach ($chain as $setting) {
            $attempt++;
            $result = $this->driver->send($setting, $message);
            $requestPayload = array_merge($result->requestPayload, [
                'integration_setting_id' => $setting->id,
                'failover_attempt' => $attempt,
            ]);
            $responsePayload = array_merge($result->responsePayload, array_filter([
                'failover_attempt' => $attempt,
                'error' => $result->error,
            ], fn ($value) => $value !== null));

            SmsProviderLog::query()->create([
                'sms_message_id' => $message->id,
                'provider' => $setting->provider->value,
                'request_payload' => $requestPayload,
                'response_payload' => $responsePayload,
                'http_status' => $result->httpStatus,
                'provider_message_id' => $result->providerMessageId,
            ]);

            if ($result->success) {
                $setting->increment('sms_sent_today');
                $setting->increment('sms_sent_month');
                $setting->update([
                    'health_status' => 'healthy',
                    'last_health_check_at' => now(),
                ]);

                return [
                    'success' => true,
                    'delivery_status' => SmsDeliveryStatus::Delivered,
                    'provider_message_id' => $result->providerMessageId,
                    'response' => $responsePayload,
                ];
            }

            $setting->increment('failed_count');
            $setting->update([
                'health_status' => 'unhealthy',
                'last_health_check_at' => now(),
            ]);

            $lastFailure = $result;
        }

        return [
            'success' => false,
            'delivery_status' => SmsDeliveryStatus::Failed,
            'provider_message_id' => $lastFailure?->providerMessageId,
            'response' => array_merge($lastFailure?->responsePayload ?? [], [
                'failover_attempt' => $attempt,
                'status' => 'failed',
                'error' => $lastFailure?->error ?? __('All SMS integrations failed.'),
            ]),
        ];
    }

    /**
     * @return array{success: bool, delivery_status: SmsDeliveryStatus, provider_message_id: ?string, response: array<string, mixed>}
     */
    protected function sendStub(SmsMessage $message): array
    {
        $payload = [
            'to' => $message->phone_number,
            'body' => $message->message_body,
            'segments' => $message->segments_count,
            'failover_attempt' => 1,
        ];

        $success = strlen((string) $message->phone_number) >= 9 && $message->message_body !== '';
        $deliveryStatus = $success ? SmsDeliveryStatus::Delivered : SmsDeliveryStatus::Rejected;
        $providerId = 'JP-'.Str::upper(Str::random(12));

        $response = [
            'status' => $success ? 'delivered' : 'rejected',
            'message_id' => $providerId,
            'timestamp' => now()->toIso8601String(),
            'failover_attempt' => 1,
            'provider' => 'jana_stub',
            'reason' => 'no_integration_configured',
        ];

        SmsProviderLog::query()->create([
            'sms_message_id' => $message->id,
            'provider' => 'jana_stub',
            'request_payload' => $payload,
            'response_payload' => $response,
            'http_status' => $success ? 200 : 422,
            'provider_message_id' => $providerId,
        ]);

        return [
            'success' => $success,
            'delivery_status' => $deliveryStatus,
            'provider_message_id' => $providerId,
            'response' => $response,
        ];
    }
}

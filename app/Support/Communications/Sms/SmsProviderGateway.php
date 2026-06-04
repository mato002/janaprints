<?php

namespace App\Support\Communications\Sms;

use App\Enums\SmsDeliveryStatus;
use App\Models\Communications\SmsMessage;
use App\Models\Communications\SmsProviderLog;
use Illuminate\Support\Str;

/**
 * Internal stub provider — replace with real SMS API in a later integration phase.
 */
class SmsProviderGateway
{
    /**
     * @return array{success: bool, delivery_status: SmsDeliveryStatus, provider_message_id: ?string, response: array<string, mixed>}
     */
    public function send(SmsMessage $message): array
    {
        $payload = [
            'to' => $message->phone_number,
            'body' => $message->message_body,
            'segments' => $message->segments_count,
        ];

        $success = strlen($message->phone_number) >= 9 && $message->message_body !== '';
        $deliveryStatus = $success ? SmsDeliveryStatus::Delivered : SmsDeliveryStatus::Rejected;
        $providerId = 'JP-'.Str::upper(Str::random(12));

        $response = [
            'status' => $success ? 'delivered' : 'rejected',
            'message_id' => $providerId,
            'timestamp' => now()->toIso8601String(),
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

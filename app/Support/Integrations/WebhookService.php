<?php

namespace App\Support\Integrations;

use App\Enums\IntegrationWebhookEvent;
use App\Enums\IntegrationWebhookStatus;
use App\Models\Integrations\IntegrationWebhook;
use App\Models\Integrations\IntegrationWebhookDelivery;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WebhookService
{
    public function __construct(
        protected IntegrationAuditService $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function dispatchForCompany(int $companyId, IntegrationWebhookEvent $event, array $payload): void
    {
        IntegrationWebhook::query()
            ->where('company_id', $companyId)
            ->where('status', IntegrationWebhookStatus::Active)
            ->get()
            ->filter(fn (IntegrationWebhook $webhook) => in_array($event->value, $webhook->event_types ?? [], true))
            ->each(fn (IntegrationWebhook $webhook) => $this->deliver($webhook, $event->value, $payload));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function save(IntegrationWebhook $webhook, array $data, int $userId): IntegrationWebhook
    {
        $old = $webhook->exists ? $webhook->getOriginal() : [];

        if (empty($data['secret'])) {
            unset($data['secret']);
        } elseif ($data['secret'] === '__generate__') {
            $data['secret'] = Str::random(32);
        }

        $data['created_by'] = $webhook->exists ? $webhook->created_by : $userId;
        $webhook->fill($data);
        $webhook->save();

        $this->audit->logChange($webhook, $webhook->wasRecentlyCreated ? 'created' : 'updated', $old, $webhook->getAttributes());

        return $webhook;
    }

    public function disable(IntegrationWebhook $webhook): void
    {
        $old = $webhook->getOriginal();
        $webhook->update(['status' => IntegrationWebhookStatus::Disabled]);
        $this->audit->logChange($webhook, 'disabled', $old, $webhook->getAttributes());
    }

    public function enable(IntegrationWebhook $webhook): void
    {
        $old = $webhook->getOriginal();
        $webhook->update(['status' => IntegrationWebhookStatus::Active]);
        $this->audit->logChange($webhook, 'enabled', $old, $webhook->getAttributes());
    }

    public function test(IntegrationWebhook $webhook): IntegrationWebhookDelivery
    {
        return $this->deliver($webhook, 'test.ping', [
            'event' => 'test.ping',
            'timestamp' => now()->toIso8601String(),
            'message' => 'Webhook test from Jana Prints ERP',
        ]);
    }

    public function retry(IntegrationWebhookDelivery $delivery): IntegrationWebhookDelivery
    {
        $webhook = $delivery->webhook;

        return $this->deliver($webhook, $delivery->event_type, $delivery->payload, $delivery);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function deliver(
        IntegrationWebhook $webhook,
        string $eventType,
        array $payload,
        ?IntegrationWebhookDelivery $existing = null,
    ): IntegrationWebhookDelivery {
        $delivery = $existing ?? IntegrationWebhookDelivery::query()->create([
            'webhook_id' => $webhook->id,
            'event_type' => $eventType,
            'payload' => $payload,
            'status' => 'pending',
            'attempt_count' => 0,
        ]);

        $signature = hash_hmac('sha256', json_encode($payload), $webhook->secret ?? '');

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'X-Jana-Signature' => $signature,
                    'X-Jana-Event' => $eventType,
                    'Content-Type' => 'application/json',
                ])
                ->post($webhook->endpoint_url, $payload);

            $success = $response->successful();
            $delivery->update([
                'response_code' => $response->status(),
                'response_body' => Str::limit($response->body(), 2000),
                'status' => $success ? 'success' : 'failed',
                'attempt_count' => $delivery->attempt_count + 1,
                'delivered_at' => now(),
            ]);

            $webhook->update([
                'last_delivery_at' => now(),
                'last_response_code' => $response->status(),
            ]);
        } catch (\Throwable $e) {
            $delivery->update([
                'status' => 'failed',
                'response_body' => $e->getMessage(),
                'attempt_count' => $delivery->attempt_count + 1,
                'delivered_at' => now(),
            ]);

            $webhook->update([
                'last_delivery_at' => now(),
                'last_response_code' => 0,
            ]);
        }

        return $delivery->fresh();
    }

    public function stats(IntegrationWebhook $webhook): array
    {
        $deliveries = $webhook->deliveries()->latest()->limit(100)->get();
        $total = $deliveries->count();
        $success = $deliveries->where('status', 'success')->count();
        $failed = $deliveries->where('status', 'failed')->count();

        return [
            'total' => $total,
            'success' => $success,
            'failed' => $failed,
            'success_rate' => $total > 0 ? round(($success / $total) * 100, 1) : 0,
        ];
    }
}

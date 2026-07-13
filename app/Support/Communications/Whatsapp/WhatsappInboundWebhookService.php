<?php

namespace App\Support\Communications\Whatsapp;

use App\Enums\WhatsappAccountStatus;
use App\Enums\WhatsappConversationStatus;
use App\Enums\WhatsappDeliveryStatus;
use App\Enums\WhatsappProvider;
use App\Enums\WhatsappVerificationStatus;
use App\Models\Communications\WhatsappAccount;
use App\Models\Communications\WhatsappConversation;
use App\Models\Communications\WhatsappMessage;
use App\Models\Communications\WhatsappParticipant;
use App\Models\Crm\Customer;
use App\Models\Integrations\IntegrationWhatsappSetting;
use Illuminate\Support\Facades\Log;

class WhatsappInboundWebhookService
{
    public function __construct(
        protected WhatsappMessageService $messages,
    ) {}

    public function verify(string $mode, string $token, string $challenge): ?string
    {
        if ($mode !== 'subscribe') {
            return null;
        }

        $matches = IntegrationWhatsappSetting::query()
            ->where('is_active', true)
            ->where('webhook_verify_token', $token)
            ->exists();

        return $matches ? $challenge : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handleMetaPayload(array $payload): int
    {
        $recorded = 0;

        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $value = $change['value'] ?? [];
                $phoneNumberId = (string) data_get($value, 'metadata.phone_number_id', '');
                $setting = $this->resolveSetting($phoneNumberId);

                if (! $setting) {
                    Log::warning('WhatsApp inbound webhook: no matching integration setting.', [
                        'phone_number_id' => $phoneNumberId,
                    ]);

                    continue;
                }

                $account = $this->resolveAccount($setting);

                foreach ($value['messages'] ?? [] as $incoming) {
                    if (($incoming['type'] ?? 'text') !== 'text') {
                        continue;
                    }

                    $from = (string) ($incoming['from'] ?? '');
                    $body = (string) data_get($incoming, 'text.body', '');

                    if ($from === '' || $body === '') {
                        continue;
                    }

                    $conversation = $this->findOrCreateConversation($account, $from, $body);
                    $this->messages->recordIncoming($conversation, $body, [
                        'provider' => 'meta_cloud',
                        'webhook' => $incoming,
                        'integration_setting_id' => $setting->id,
                    ]);
                    $recorded++;
                }

                foreach ($value['statuses'] ?? [] as $status) {
                    $this->applyDeliveryStatus($status);
                }
            }
        }

        return $recorded;
    }

    protected function resolveSetting(string $phoneNumberId): ?IntegrationWhatsappSetting
    {
        if ($phoneNumberId === '') {
            return IntegrationWhatsappSetting::query()
                ->where('is_active', true)
                ->orderByDesc('id')
                ->first();
        }

        return IntegrationWhatsappSetting::query()
            ->where('phone_number_id', $phoneNumberId)
            ->orderByDesc('is_active')
            ->first();
    }

    protected function resolveAccount(IntegrationWhatsappSetting $setting): WhatsappAccount
    {
        $provider = match ($setting->provider->value) {
            'meta_cloud' => WhatsappProvider::MetaCloud,
            'twilio' => WhatsappProvider::Twilio,
            'africas_talking' => WhatsappProvider::AfricasTalking,
            default => WhatsappProvider::Custom,
        };

        $existing = WhatsappAccount::query()
            ->where('company_id', $setting->company_id)
            ->where(function ($query) use ($setting) {
                $query->where('provider_account_ref', $setting->phone_number_id)
                    ->orWhere('phone_number', $setting->sender_phone);
            })
            ->first();

        if ($existing) {
            return $existing;
        }

        $default = WhatsappAccount::query()
            ->where('company_id', $setting->company_id)
            ->where('is_default', true)
            ->first();

        if ($default) {
            $default->update([
                'provider' => $provider,
                'provider_account_ref' => $setting->phone_number_id,
                'phone_number' => $setting->sender_phone ?: $default->phone_number,
                'status' => WhatsappAccountStatus::Active,
                'verification_status' => WhatsappVerificationStatus::Verified,
            ]);

            return $default->fresh();
        }

        return WhatsappAccount::query()->create([
            'company_id' => $setting->company_id,
            'name' => __('WhatsApp inbound'),
            'phone_number' => $setting->sender_phone ?: '+0000000000',
            'display_name' => config('app.name'),
            'provider' => $provider,
            'provider_account_ref' => $setting->phone_number_id,
            'status' => WhatsappAccountStatus::Active,
            'verification_status' => WhatsappVerificationStatus::Verified,
            'is_default' => true,
        ]);
    }

    protected function findOrCreateConversation(WhatsappAccount $account, string $from, string $preview): WhatsappConversation
    {
        $normalized = str_starts_with($from, '+') ? $from : '+'.$from;

        $conversation = WhatsappConversation::query()
            ->where('company_id', $account->company_id)
            ->where('whatsapp_account_id', $account->id)
            ->where(function ($query) use ($normalized, $from) {
                $query->where('phone_number', $normalized)
                    ->orWhere('phone_number', $from)
                    ->orWhere('phone_number', ltrim($normalized, '+'));
            })
            ->first();

        if ($conversation) {
            return $conversation;
        }

        $customer = Customer::query()
            ->where('company_id', $account->company_id)
            ->where(function ($query) use ($normalized, $from) {
                $query->where('phone', $normalized)
                    ->orWhere('phone', $from)
                    ->orWhere('phone', ltrim($normalized, '+'));
            })
            ->first();

        $conversation = WhatsappConversation::query()->create([
            'company_id' => $account->company_id,
            'branch_id' => $account->branch_id,
            'whatsapp_account_id' => $account->id,
            'conversation_code' => 'WA-IN-'.now()->format('ymdHis').'-'.$account->id,
            'phone_number' => $normalized,
            'customer_id' => $customer?->id,
            'status' => WhatsappConversationStatus::Open,
            'started_at' => now(),
            'last_activity_at' => now(),
            'last_message_preview' => mb_substr($preview, 0, 200),
            'unread_count' => 0,
        ]);

        WhatsappParticipant::query()->create([
            'whatsapp_conversation_id' => $conversation->id,
            'participant_type' => $customer ? 'customer' : 'external',
            'participant_id' => $customer?->id,
            'phone_number' => $normalized,
            'display_name' => $customer?->company_name ?? $normalized,
            'role' => 'contact',
        ]);

        return $conversation;
    }

    /**
     * @param  array<string, mixed>  $status
     */
    protected function applyDeliveryStatus(array $status): void
    {
        $providerRef = (string) ($status['id'] ?? '');
        if ($providerRef === '') {
            return;
        }

        $message = WhatsappMessage::query()
            ->where('provider_message_ref', $providerRef)
            ->first();

        if (! $message) {
            return;
        }

        $mapped = match ((string) ($status['status'] ?? '')) {
            'sent' => WhatsappDeliveryStatus::Sent,
            'delivered' => WhatsappDeliveryStatus::Delivered,
            'read' => WhatsappDeliveryStatus::Read,
            'failed' => WhatsappDeliveryStatus::Failed,
            default => null,
        };

        if ($mapped === null) {
            return;
        }

        $message->update([
            'status' => $mapped,
            'delivered_at' => $mapped === WhatsappDeliveryStatus::Delivered ? now() : $message->delivered_at,
            'failed_at' => $mapped === WhatsappDeliveryStatus::Failed ? now() : $message->failed_at,
            'provider_response' => array_merge($message->provider_response ?? [], ['webhook_status' => $status]),
        ]);

        $this->messages->recordDeliveryEvent($message, 'webhook_status', $mapped->value, $status);
    }
}

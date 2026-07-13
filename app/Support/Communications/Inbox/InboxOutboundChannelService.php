<?php

namespace App\Support\Communications\Inbox;

use App\Enums\EmailDeliveryStatus;
use App\Enums\InboxMessageChannel;
use App\Enums\SmsCampaignSendMode;
use App\Enums\SmsCampaignStatus;
use App\Enums\SmsDeliveryStatus;
use App\Enums\SmsMessageQueueStatus;
use App\Enums\SmsRecipientSource;
use App\Enums\WhatsappDeliveryStatus;
use App\Models\Communications\Inbox\CommunicationConversation;
use App\Models\Communications\SmsCampaign;
use App\Models\Communications\SmsMessage;
use App\Models\Communications\SmsRecipient;
use App\Support\Communications\Email\EmailMessageService;
use App\Support\Communications\Sms\SmsProviderGateway;
use App\Support\Communications\Whatsapp\WhatsappConversationService;
use App\Support\Communications\Whatsapp\WhatsappMessageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InboxOutboundChannelService
{
    public function __construct(
        protected SmsProviderGateway $smsGateway,
        protected WhatsappMessageService $whatsappMessages,
        protected WhatsappConversationService $whatsappConversations,
        protected EmailMessageService $emailMessages,
    ) {}

    /**
     * @return array{success: bool, error: ?string, source_type: ?string, source_id: ?int}
     */
    public function dispatch(
        CommunicationConversation $conversation,
        InboxMessageChannel $channel,
        string $body,
        int $userId,
    ): array {
        return match ($channel) {
            InboxMessageChannel::Sms => $this->dispatchSms($conversation, $body, $userId),
            InboxMessageChannel::WhatsApp => $this->dispatchWhatsapp($conversation, $body, $userId),
            InboxMessageChannel::Email => $this->dispatchEmail($conversation, $body, $userId),
            default => ['success' => true, 'error' => null, 'source_type' => null, 'source_id' => null],
        };
    }

    /**
     * @return array{success: bool, error: ?string, source_type: ?string, source_id: ?int}
     */
    protected function dispatchSms(CommunicationConversation $conversation, string $body, int $userId): array
    {
        $phone = trim((string) $conversation->phone_number);
        if ($phone === '') {
            throw ValidationException::withMessages([
                'channel' => __('This conversation has no phone number for SMS.'),
            ]);
        }

        $segments = (int) max(1, ceil(strlen($body) / 160));

        $message = DB::transaction(function () use ($conversation, $body, $userId, $phone, $segments) {
            $campaign = SmsCampaign::query()->create([
                'company_id' => $conversation->company_id,
                'branch_id' => $conversation->branch_id,
                'campaign_code' => 'INBOX-SMS-'.now()->format('ymdHis').'-'.$conversation->id,
                'name' => __('Inbox reply :code', ['code' => $conversation->conversation_code]),
                'message_template' => $body,
                'send_mode' => SmsCampaignSendMode::Immediate,
                'status' => SmsCampaignStatus::Sending,
                'recipient_source' => SmsRecipientSource::Manual,
                'character_count' => strlen($body),
                'estimated_segments' => $segments,
                'total_recipients' => 1,
                'created_by' => $userId,
                'sent_by' => $userId,
                'started_at' => now(),
            ]);

            $recipient = SmsRecipient::query()->create([
                'sms_campaign_id' => $campaign->id,
                'source_type' => $conversation->customer_id ? 'customer' : 'manual',
                'source_id' => $conversation->customer_id,
                'phone_number' => $phone,
                'display_name' => $conversation->display_name,
                'status' => 'queued',
            ]);

            return SmsMessage::query()->create([
                'sms_campaign_id' => $campaign->id,
                'sms_recipient_id' => $recipient->id,
                'company_id' => $conversation->company_id,
                'branch_id' => $conversation->branch_id,
                'phone_number' => $phone,
                'message_body' => $body,
                'queue_status' => SmsMessageQueueStatus::Queued,
                'delivery_status' => SmsDeliveryStatus::Queued,
                'segments_count' => $segments,
                'character_count' => strlen($body),
                'credit_cost' => $segments,
                'attempts' => 0,
            ]);
        });

        $result = $this->smsGateway->send($message);

        $message->update([
            'queue_status' => $result['success'] ? SmsMessageQueueStatus::Sent : SmsMessageQueueStatus::Failed,
            'delivery_status' => $result['delivery_status'],
            'sent_at' => $result['success'] ? now() : null,
            'delivered_at' => $result['delivery_status'] === SmsDeliveryStatus::Delivered ? now() : null,
            'failure_reason' => $result['success'] ? null : ($result['response']['error'] ?? 'failed'),
            'attempts' => 1,
            'last_attempt_at' => now(),
        ]);

        $message->campaign?->update([
            'status' => SmsCampaignStatus::Completed,
            'completed_at' => now(),
            'sent_at' => now(),
            'sent_count' => $result['success'] ? 1 : 0,
            'failed_count' => $result['success'] ? 0 : 1,
        ]);

        return [
            'success' => $result['success'],
            'error' => $result['success'] ? null : (string) ($result['response']['error'] ?? __('SMS send failed.')),
            'source_type' => SmsMessage::class,
            'source_id' => $message->id,
        ];
    }

    /**
     * @return array{success: bool, error: ?string, source_type: ?string, source_id: ?int}
     */
    protected function dispatchWhatsapp(CommunicationConversation $conversation, string $body, int $userId): array
    {
        $phone = trim((string) $conversation->phone_number);
        if ($phone === '') {
            throw ValidationException::withMessages([
                'channel' => __('This conversation has no phone number for WhatsApp.'),
            ]);
        }

        $waConversation = $this->whatsappConversations->findOrCreateForContact(
            companyId: (int) $conversation->company_id,
            phoneNumber: $phone,
            userId: $userId,
            customerId: $conversation->customer_id,
            leadId: $conversation->lead_id,
            vendorId: $conversation->vendor_id,
            displayName: $conversation->display_name,
        );

        $message = $this->whatsappMessages->sendManual($waConversation, $body, $userId);
        $success = $message->status !== WhatsappDeliveryStatus::Failed;

        return [
            'success' => $success,
            'error' => $success ? null : (string) data_get($message->provider_response, 'error', __('WhatsApp send failed.')),
            'source_type' => $message::class,
            'source_id' => $message->id,
        ];
    }

    /**
     * @return array{success: bool, error: ?string, source_type: ?string, source_id: ?int}
     */
    protected function dispatchEmail(CommunicationConversation $conversation, string $body, int $userId): array
    {
        $email = trim((string) $conversation->email);
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                'channel' => __('This conversation has no valid email address.'),
            ]);
        }

        $message = $this->emailMessages->compose((int) $conversation->company_id, $userId, [
            'to' => [['email' => $email, 'name' => $conversation->display_name]],
            'subject' => __('Re: :name', ['name' => $conversation->display_name ?: __('Conversation')]),
            'body' => nl2br(e($body)),
            'sender_purpose' => 'system_alert',
            'branch_id' => $conversation->branch_id,
            'metadata' => [
                'module' => 'communications.inbox',
                'inbox_conversation_id' => $conversation->id,
            ],
        ], true);

        $success = ! in_array($message->status, [
            EmailDeliveryStatus::Failed,
            EmailDeliveryStatus::Bounced,
            EmailDeliveryStatus::Draft,
        ], true);

        return [
            'success' => $success,
            'error' => $success ? null : (string) data_get($message->provider_response, 'error', __('Email send failed.')),
            'source_type' => $message::class,
            'source_id' => $message->id,
        ];
    }
}

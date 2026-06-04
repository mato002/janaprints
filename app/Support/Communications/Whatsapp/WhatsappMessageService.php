<?php

namespace App\Support\Communications\Whatsapp;

use App\Enums\CommunicationLogType;
use App\Enums\WhatsappDeliveryStatus;
use App\Enums\WhatsappMessageDirection;
use App\Enums\WhatsappMessageType;
use App\Models\Communications\CommunicationTemplate;
use App\Models\Communications\WhatsappConversation;
use App\Models\Communications\WhatsappDeliveryEvent;
use App\Models\Communications\WhatsappMessage;
use App\Models\Communications\WhatsappTemplate;
use App\Support\Communications\CommunicationLogService;
use App\Support\Communications\TemplateRenderer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class WhatsappMessageService
{
    public function __construct(
        protected WhatsappProviderGateway $gateway,
        protected WhatsappConversationService $conversations,
        protected CommunicationLogService $communicationLogs,
        protected TemplateRenderer $renderer,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function query(int $companyId, array $filters = []): Builder
    {
        $query = WhatsappMessage::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->with(['conversation.customer', 'account', 'creator', 'communicationTemplate']);

        if ($status = $filters['status'] ?? null) {
            $query->where('status', $status);
        }

        if (($filters['view'] ?? null) === 'queue') {
            $query->whereIn('status', [
                WhatsappDeliveryStatus::Queued,
            ]);
        }

        if (($filters['view'] ?? null) === 'failures') {
            $query->where('status', WhatsappDeliveryStatus::Failed);
        }

        if ($q = trim((string) ($filters['q'] ?? ''))) {
            $query->where('body', 'like', "%{$q}%");
        }

        return $query->orderByDesc('created_at');
    }

    public function sendManual(
        WhatsappConversation $conversation,
        string $body,
        int $userId,
        ?array $variableData = null,
    ): WhatsappMessage {
        return $this->dispatchOutgoing(
            conversation: $conversation,
            body: $body,
            messageType: WhatsappMessageType::Manual,
            userId: $userId,
            template: null,
            whatsappTemplate: null,
            variableData: $variableData,
        );
    }

    public function sendTemplate(
        WhatsappConversation $conversation,
        WhatsappTemplate $whatsappTemplate,
        int $userId,
        array $variableData = [],
    ): WhatsappMessage {
        $comTemplate = $whatsappTemplate->communicationTemplate;
        $rendered = $this->renderer->render($comTemplate->subject, $comTemplate->body, $variableData);
        $body = $rendered['body'];

        return $this->dispatchOutgoing(
            conversation: $conversation,
            body: $body,
            messageType: WhatsappMessageType::Template,
            userId: $userId,
            template: $comTemplate,
            whatsappTemplate: $whatsappTemplate,
            variableData: $variableData,
        );
    }

    public function recordIncoming(
        WhatsappConversation $conversation,
        string $body,
        ?array $providerPayload = null,
    ): WhatsappMessage {
        $message = WhatsappMessage::query()->create([
            'company_id' => $conversation->company_id,
            'branch_id' => $conversation->branch_id,
            'whatsapp_conversation_id' => $conversation->id,
            'whatsapp_account_id' => $conversation->whatsapp_account_id,
            'direction' => WhatsappMessageDirection::Incoming,
            'message_type' => WhatsappMessageType::Incoming,
            'body' => $body,
            'status' => WhatsappDeliveryStatus::Delivered,
            'delivered_at' => now(),
            'provider_response' => $providerPayload,
        ]);

        $this->recordDeliveryEvent($message, 'received', WhatsappDeliveryStatus::Delivered->value, $providerPayload);
        $this->conversations->touchActivity($conversation, $body, incoming: true);
        $this->communicationLogs->recordFromWhatsappMessage($message->fresh(['conversation.participants']));

        return $message;
    }

    protected function dispatchOutgoing(
        WhatsappConversation $conversation,
        string $body,
        WhatsappMessageType $messageType,
        int $userId,
        ?CommunicationTemplate $template,
        ?WhatsappTemplate $whatsappTemplate,
        ?array $variableData,
    ): WhatsappMessage {
        return DB::transaction(function () use ($conversation, $body, $messageType, $userId, $template, $whatsappTemplate, $variableData) {
            $message = WhatsappMessage::query()->create([
                'company_id' => $conversation->company_id,
                'branch_id' => $conversation->branch_id,
                'whatsapp_conversation_id' => $conversation->id,
                'whatsapp_account_id' => $conversation->whatsapp_account_id,
                'direction' => WhatsappMessageDirection::Outgoing,
                'message_type' => $messageType,
                'body' => $body,
                'communication_template_id' => $template?->id,
                'whatsapp_template_id' => $whatsappTemplate?->id,
                'status' => WhatsappDeliveryStatus::Queued,
                'queued_at' => now(),
                'created_by' => $userId,
            ]);

            $this->recordDeliveryEvent($message, 'queued', WhatsappDeliveryStatus::Queued->value, $variableData);
            $this->conversations->touchActivity($conversation, $body);

            $account = $conversation->account;
            $result = $this->gateway->send($account, $message);

            $message->update([
                'status' => $result->status,
                'provider_message_ref' => $result->providerMessageRef,
                'provider_response' => $result->payload,
                'sent_at' => $result->status === WhatsappDeliveryStatus::Sent ? now() : null,
                'failed_at' => $result->status === WhatsappDeliveryStatus::Failed ? now() : null,
            ]);

            $this->recordDeliveryEvent($message, 'provider_response', $result->status->value, $result->payload);
            $this->communicationLogs->recordFromWhatsappMessage($message->fresh(['conversation.participants', 'communicationTemplate']));

            return $message->fresh();
        });
    }

    public function recordDeliveryEvent(
        WhatsappMessage $message,
        string $event,
        ?string $statusSnapshot = null,
        ?array $payload = null,
        ?int $createdBy = null,
    ): WhatsappDeliveryEvent {
        return WhatsappDeliveryEvent::query()->create([
            'whatsapp_message_id' => $message->id,
            'event' => $event,
            'status_snapshot' => $statusSnapshot,
            'payload' => $payload,
            'created_by' => $createdBy,
        ]);
    }
}

<?php

namespace App\Support\Communications\Email;

use App\Enums\EmailDeliveryStatus;
use App\Models\Communications\EmailMessage;
use App\Support\Communications\CommunicationLogService;
use Illuminate\Support\Facades\DB;

class CorporateMailAuditService
{
    public function __construct(
        protected CommunicationLogService $communicationLogs,
        protected EmailAccountService $accounts,
    ) {}

    /**
     * Record an outbound email in the audit trail without sending.
     *
     * @param  array{
     *     company_id: int,
     *     user_id: int,
     *     to: list<array{email: string, name?: string}>,
     *     subject: string,
     *     body: string,
     *     cc?: list<array{email: string, name?: string}>,
     *     bcc?: list<array{email: string, name?: string}>,
     *     communication_template_id?: int|null,
     *     email_template_id?: int|null,
     *     sender_purpose?: string,
     *     status?: EmailDeliveryStatus,
     *     sent_at?: \DateTimeInterface|null,
     *     delivered_at?: \DateTimeInterface|null,
     *     failed_at?: \DateTimeInterface|null,
     *     failure_reason?: string|null,
     *     provider_message_ref?: string|null,
     *     provider_response?: array|null,
     * }  $payload
     */
    public function recordOutbound(array $payload): EmailMessage
    {
        return DB::transaction(function () use ($payload) {
            $purpose = (string) ($payload['sender_purpose'] ?? 'system_alert');
            $account = $this->accounts->accountForPurpose(
                (int) $payload['company_id'],
                (int) $payload['user_id'],
                $purpose,
            );

            $status = $payload['status'] ?? EmailDeliveryStatus::Sent;

            $message = EmailMessage::query()->create([
                'company_id' => $payload['company_id'],
                'branch_id' => tenant()->branchId(),
                'email_account_id' => $account->id,
                'to_emails' => $payload['to'],
                'cc_emails' => $payload['cc'] ?? [],
                'bcc_emails' => $payload['bcc'] ?? [],
                'subject' => $payload['subject'],
                'body' => $payload['body'],
                'communication_template_id' => $payload['communication_template_id'] ?? null,
                'email_template_id' => $payload['email_template_id'] ?? null,
                'status' => $status,
                'provider_message_ref' => $payload['provider_message_ref'] ?? null,
                'provider_response' => $payload['provider_response'] ?? null,
                'failure_reason' => $payload['failure_reason'] ?? null,
                'queued_at' => $status === EmailDeliveryStatus::Queued ? now() : null,
                'sent_at' => $payload['sent_at'] ?? ($status === EmailDeliveryStatus::Sent ? now() : null),
                'delivered_at' => $payload['delivered_at'] ?? null,
                'failed_at' => $payload['failed_at'] ?? ($status === EmailDeliveryStatus::Failed ? now() : null),
                'created_by' => $payload['user_id'],
            ]);

            $this->communicationLogs->recordFromEmailMessage(
                $message->fresh(['attachments', 'recipient', 'communicationTemplate']),
            );

            return $message->fresh(['attachments', 'recipient', 'communicationTemplate']);
        });
    }
}

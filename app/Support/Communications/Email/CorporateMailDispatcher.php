<?php

namespace App\Support\Communications\Email;

use App\Models\Communications\EmailMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;

class CorporateMailDispatcher
{
    public function __construct(
        protected EmailMessageService $messages,
    ) {}

    /**
     * @param  array{
     *     company_id: int,
     *     user_id: int,
     *     to: list<array{email: string, name?: string|null}>,
     *     subject: string,
     *     body: string,
     *     sender_purpose: string,
     *     branch_id?: int|null,
     *     cc?: list<array{email: string, name?: string|null}>,
     *     bcc?: list<array{email: string, name?: string|null}>,
     *     attachments?: list<array{attachment_type: string, attachable_type?: string, attachable_id?: int, label?: string, file_path?: string}>,
     *     metadata?: array<string, mixed>,
     *     communication_template_id?: int|null,
     *     email_template_id?: int|null,
     * }  $payload
     */
    public function dispatch(array $payload, bool $sendNow = true): ?EmailMessage
    {
        if ($payload['to'] === []) {
            return null;
        }

        try {
            return $this->messages->compose(
                (int) $payload['company_id'],
                (int) $payload['user_id'],
                [
                    'to' => $payload['to'],
                    'cc' => $payload['cc'] ?? [],
                    'bcc' => $payload['bcc'] ?? [],
                    'subject' => $payload['subject'],
                    'body' => $payload['body'],
                    'sender_purpose' => $payload['sender_purpose'],
                    'attachments' => $payload['attachments'] ?? [],
                    'communication_template_id' => $payload['communication_template_id'] ?? null,
                    'email_template_id' => $payload['email_template_id'] ?? null,
                    'metadata' => $payload['metadata'] ?? [],
                    'branch_id' => $payload['branch_id'] ?? null,
                ],
                sendNow: $sendNow,
            );
        } catch (\Throwable $e) {
            Log::warning('Corporate mail dispatch failed.', [
                'sender_purpose' => $payload['sender_purpose'] ?? null,
                'subject' => $payload['subject'] ?? null,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function dispatchMailable(array $payload, Mailable $mailable, bool $sendNow = true): ?EmailMessage
    {
        return $this->dispatch(array_merge($payload, [
            'subject' => $mailable->envelope()->subject,
            'body' => $mailable->render(),
        ]), $sendNow);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function dispatchView(array $payload, string $view, array $data = [], bool $sendNow = true): ?EmailMessage
    {
        return $this->dispatch(array_merge($payload, [
            'body' => view($view, $data)->render(),
        ]), $sendNow);
    }
}

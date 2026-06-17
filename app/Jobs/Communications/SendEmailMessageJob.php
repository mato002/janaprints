<?php

namespace App\Jobs\Communications;

use App\Enums\EmailDeliveryStatus;
use App\Jobs\PlatformJob;
use App\Models\Communications\EmailMessage;
use App\Support\Communications\Email\EmailMessageService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmailMessageJob extends PlatformJob implements ShouldQueue
{
    public int $tries = 3;

    public function __construct(
        public int $messageId,
    ) {
        parent::__construct();
        $this->useQueue('emails');
    }

    public function handle(EmailMessageService $messages): void
    {
        $message = EmailMessage::query()->find($this->messageId);

        if ($message === null) {
            return;
        }

        if (! $this->isDeliverable($message)) {
            return;
        }

        try {
            $messages->deliver($message);
        } catch (\Throwable $e) {
            $message->refresh();

            if ($message->status !== EmailDeliveryStatus::Failed) {
                $messages->markFailed($message, $e->getMessage());
            }

            throw $e;
        }
    }

    protected function isDeliverable(EmailMessage $message): bool
    {
        return in_array($message->status, [
            EmailDeliveryStatus::Queued,
            EmailDeliveryStatus::Sending,
        ], true);
    }
}

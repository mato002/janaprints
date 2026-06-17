<?php

namespace App\Notifications\Channels;

use App\Support\Communications\Email\CorporateMailDispatcher;
use Illuminate\Notifications\Notification;

class CorporateMailNotificationChannel
{
    public function __construct(
        protected CorporateMailDispatcher $dispatcher,
    ) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toCorporateMail')) {
            return;
        }

        $payload = $notification->toCorporateMail($notifiable);

        if ($payload === null) {
            return;
        }

        $message = $this->dispatcher->dispatch($payload);

        if ($message === null) {
            throw new \RuntimeException(__('Unable to queue the email message.'));
        }
    }
}

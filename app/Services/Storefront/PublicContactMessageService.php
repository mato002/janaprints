<?php

namespace App\Services\Storefront;

use App\Mail\PublicContactMessageConfirmationMail;
use App\Mail\PublicContactMessageInternalNotificationMail;
use App\Models\PublicContactMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PublicContactMessageService
{
    public function store(array $data): PublicContactMessage
    {
        $message = PublicContactMessage::query()->create([
            'name' => $data['name'],
            'company' => $data['company'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'source' => 'storefront',
        ]);

        $this->dispatchEmails($message);

        return $message;
    }

    protected function dispatchEmails(PublicContactMessage $message): void
    {
        try {
            Mail::to($message->email)->send(new PublicContactMessageConfirmationMail($message));
        } catch (\Throwable $e) {
            Log::warning('Failed to send contact message confirmation email.', [
                'contact_message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);
        }

        $adminEmail = config('leads.admin_email');

        if (! $adminEmail) {
            return;
        }

        try {
            Mail::to($adminEmail)->send(new PublicContactMessageInternalNotificationMail($message));
        } catch (\Throwable $e) {
            Log::warning('Failed to send contact message internal notification.', [
                'contact_message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

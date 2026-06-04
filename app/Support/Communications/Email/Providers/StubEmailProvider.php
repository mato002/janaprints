<?php

namespace App\Support\Communications\Email\Providers;

use App\Enums\EmailDeliveryStatus;
use App\Models\Communications\EmailAccount;
use App\Models\Communications\EmailMessage;
use App\Support\Communications\Email\Contracts\EmailProviderContract;
use App\Support\Communications\Email\EmailProviderResult;

class StubEmailProvider implements EmailProviderContract
{
    public function send(EmailAccount $account, EmailMessage $message): EmailProviderResult
    {
        return new EmailProviderResult(
            status: EmailDeliveryStatus::Queued,
            providerMessageRef: 'email-stub-'.uniqid(),
            payload: [
                'provider' => $account->provider->value,
                'simulated' => true,
                'note' => 'Mail provider not connected — message queued locally.',
            ],
        );
    }
}

<?php

namespace App\Support\Communications\Email;

use App\Models\Communications\EmailAccount;
use App\Models\Communications\EmailMessage;
use App\Support\Communications\Email\Providers\IntegrationBridgedEmailProvider;

class EmailProviderGateway
{
    public function __construct(
        protected IntegrationBridgedEmailProvider $provider,
    ) {}

    public function send(EmailAccount $account, EmailMessage $message): EmailProviderResult
    {
        return $this->provider->send($account, $message);
    }
}

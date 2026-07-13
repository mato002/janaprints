<?php

namespace App\Support\Communications\Whatsapp;

use App\Models\Communications\WhatsappAccount;
use App\Models\Communications\WhatsappMessage;
use App\Support\Communications\Whatsapp\Providers\IntegrationBridgedWhatsappProvider;

class WhatsappProviderGateway
{
    public function __construct(
        protected IntegrationBridgedWhatsappProvider $provider,
    ) {}

    public function send(WhatsappAccount $account, WhatsappMessage $message): WhatsappProviderResult
    {
        return $this->provider->send($account, $message);
    }
}

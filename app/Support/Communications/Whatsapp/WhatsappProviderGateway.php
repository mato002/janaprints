<?php

namespace App\Support\Communications\Whatsapp;

use App\Enums\WhatsappProvider;
use App\Models\Communications\WhatsappAccount;
use App\Models\Communications\WhatsappMessage;
use App\Support\Communications\Whatsapp\Contracts\WhatsappProviderContract;
use App\Support\Communications\Whatsapp\Providers\StubWhatsappProvider;

class WhatsappProviderGateway
{
    public function __construct(
        protected StubWhatsappProvider $stub,
    ) {}

    public function send(WhatsappAccount $account, WhatsappMessage $message): WhatsappProviderResult
    {
        return $this->resolve($account)->send($account, $message);
    }

    protected function resolve(WhatsappAccount $account): WhatsappProviderContract
    {
        return match ($account->provider) {
            WhatsappProvider::MetaCloud,
            WhatsappProvider::Twilio,
            WhatsappProvider::AfricasTalking,
            WhatsappProvider::Custom,
            WhatsappProvider::Unconfigured => $this->stub,
        };
    }
}

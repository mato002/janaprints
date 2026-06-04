<?php

namespace App\Support\Communications\Whatsapp\Providers;

use App\Enums\WhatsappDeliveryStatus;
use App\Models\Communications\WhatsappAccount;
use App\Models\Communications\WhatsappMessage;
use App\Support\Communications\Whatsapp\Contracts\WhatsappProviderContract;
use App\Support\Communications\Whatsapp\WhatsappProviderResult;

/**
 * No external API — simulates provider acceptance for foundation testing.
 */
class StubWhatsappProvider implements WhatsappProviderContract
{
    public function send(WhatsappAccount $account, WhatsappMessage $message): WhatsappProviderResult
    {
        return new WhatsappProviderResult(
            status: WhatsappDeliveryStatus::Queued,
            providerMessageRef: 'stub-'.uniqid(),
            payload: [
                'provider' => $account->provider->value,
                'simulated' => true,
                'note' => 'Provider not connected — message queued locally.',
            ],
        );
    }
}

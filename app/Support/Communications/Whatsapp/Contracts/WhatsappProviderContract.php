<?php

namespace App\Support\Communications\Whatsapp\Contracts;

use App\Models\Communications\WhatsappAccount;
use App\Models\Communications\WhatsappMessage;
use App\Support\Communications\Whatsapp\WhatsappProviderResult;

/**
 * Provider adapter boundary — business workflows depend only on this contract.
 */
interface WhatsappProviderContract
{
    public function send(WhatsappAccount $account, WhatsappMessage $message): WhatsappProviderResult;
}

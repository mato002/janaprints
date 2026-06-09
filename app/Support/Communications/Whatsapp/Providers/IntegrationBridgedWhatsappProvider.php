<?php

namespace App\Support\Communications\Whatsapp\Providers;

use App\Enums\WhatsappDeliveryStatus;
use App\Models\Communications\WhatsappAccount;
use App\Models\Communications\WhatsappMessage;
use App\Support\Communications\Bridge\IntegrationProviderResolver;
use App\Support\Communications\Bridge\IntegrationWhatsappDriver;
use App\Support\Communications\Whatsapp\Contracts\WhatsappProviderContract;
use App\Support\Communications\Whatsapp\WhatsappProviderResult;

class IntegrationBridgedWhatsappProvider implements WhatsappProviderContract
{
    public function __construct(
        protected IntegrationProviderResolver $resolver,
        protected IntegrationWhatsappDriver $driver,
        protected StubWhatsappProvider $fallback,
    ) {}

    public function send(WhatsappAccount $account, WhatsappMessage $message): WhatsappProviderResult
    {
        $chain = $this->resolver->whatsappChain($account->company_id, $account->provider);

        if ($chain->isEmpty()) {
            return $this->fallback->send($account, $message);
        }

        $lastResult = null;
        $attempt = 0;

        foreach ($chain as $setting) {
            $attempt++;
            $result = $this->driver->send($setting, $message);
            $payload = array_merge($result->payload ?? [], [
                'failover_attempt' => $attempt,
                'account_provider' => $account->provider->value,
            ]);

            if ($result->status !== WhatsappDeliveryStatus::Failed) {
                return new WhatsappProviderResult(
                    status: $result->status,
                    providerMessageRef: $result->providerMessageRef,
                    payload: $payload,
                    error: $result->error,
                );
            }

            $lastResult = new WhatsappProviderResult(
                status: $result->status,
                providerMessageRef: $result->providerMessageRef,
                payload: $payload,
                error: $result->error,
            );
        }

        return $lastResult ?? $this->fallback->send($account, $message);
    }
}

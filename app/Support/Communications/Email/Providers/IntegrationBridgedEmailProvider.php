<?php

namespace App\Support\Communications\Email\Providers;

use App\Enums\EmailDeliveryStatus;
use App\Models\Communications\EmailAccount;
use App\Models\Communications\EmailMessage;
use App\Support\Communications\Bridge\IntegrationEmailDriver;
use App\Support\Communications\Bridge\IntegrationProviderResolver;
use App\Support\Communications\Email\Contracts\EmailProviderContract;
use App\Support\Communications\Email\EmailProviderResult;

class IntegrationBridgedEmailProvider implements EmailProviderContract
{
    public function __construct(
        protected IntegrationProviderResolver $resolver,
        protected IntegrationEmailDriver $driver,
        protected StubEmailProvider $fallback,
    ) {}

    public function send(EmailAccount $account, EmailMessage $message): EmailProviderResult
    {
        $chain = $this->resolver->emailChain($account->company_id);

        if ($chain->isEmpty()) {
            return $this->fallback->send($account, $message);
        }

        $lastResult = null;
        $attempt = 0;

        foreach ($chain as $setting) {
            $attempt++;
            $result = $this->driver->send($setting, $account, $message);
            $payload = array_merge($result->payload ?? [], [
                'failover_attempt' => $attempt,
            ]);

            if ($result->status !== EmailDeliveryStatus::Failed) {
                return new EmailProviderResult(
                    status: $result->status,
                    providerMessageRef: $result->providerMessageRef,
                    payload: $payload,
                    error: $result->error,
                );
            }

            $lastResult = new EmailProviderResult(
                status: $result->status,
                providerMessageRef: $result->providerMessageRef,
                payload: $payload,
                error: $result->error,
            );
        }

        return $lastResult ?? $this->fallback->send($account, $message);
    }
}

<?php

namespace App\Support\Integrations;

use App\Models\Integrations\IntegrationApiKey;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiKeyService
{
    public function __construct(
        protected IntegrationAuditService $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{apiKey: IntegrationApiKey, secret: string}
     */
    public function generate(int $companyId, array $data, int $userId): array
    {
        $key = 'jp_'.Str::random(32);
        $secret = 'jps_'.Str::random(48);

        $apiKey = IntegrationApiKey::query()->create([
            'company_id' => $companyId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'key' => $key,
            'secret_prefix' => substr($secret, 0, 8),
            'secret_hash' => Hash::make($secret),
            'environment' => $data['environment'],
            'allowed_ips' => $data['allowed_ips'] ?? null,
            'permissions' => $data['permissions'] ?? [],
            'is_active' => true,
            'created_by' => $userId,
        ]);

        $this->audit->logChange($apiKey, 'generated', [], [
            'name' => $apiKey->name,
            'key' => $apiKey->key,
            'environment' => $apiKey->environment->value,
        ]);

        return ['apiKey' => $apiKey, 'secret' => $secret];
    }

    /**
     * @return array{apiKey: IntegrationApiKey, secret: string}
     */
    public function regenerate(IntegrationApiKey $apiKey, int $userId): array
    {
        $secret = 'jps_'.Str::random(48);
        $old = $apiKey->getOriginal();

        $apiKey->update([
            'secret_prefix' => substr($secret, 0, 8),
            'secret_hash' => Hash::make($secret),
            'is_active' => true,
            'revoked_at' => null,
            'revoked_by' => null,
        ]);

        $this->audit->logChange($apiKey, 'regenerated', $old, $apiKey->getAttributes());

        return ['apiKey' => $apiKey->fresh(), 'secret' => $secret];
    }

    public function disable(IntegrationApiKey $apiKey, int $userId): void
    {
        $old = $apiKey->getOriginal();
        $apiKey->update(['is_active' => false]);
        $this->audit->logChange($apiKey, 'disabled', $old, $apiKey->getAttributes());
    }

    public function enable(IntegrationApiKey $apiKey, int $userId): void
    {
        $old = $apiKey->getOriginal();
        $apiKey->update(['is_active' => true]);
        $this->audit->logChange($apiKey, 'enabled', $old, $apiKey->getAttributes());
    }

    public function revoke(IntegrationApiKey $apiKey, int $userId): void
    {
        $old = $apiKey->getOriginal();
        $apiKey->update([
            'is_active' => false,
            'revoked_at' => now(),
            'revoked_by' => $userId,
        ]);
        $this->audit->logChange($apiKey, 'revoked', $old, $apiKey->getAttributes());
    }
}

<?php

namespace App\Support\Integrations;

use App\Enums\IntegrationProviderStatus;
use App\Models\Integrations\IntegrationProvider;
use App\Models\Integrations\IntegrationProviderLog;
use App\Models\Integrations\IntegrationSyncLog;

class ProviderService
{
    public function __construct(
        protected IntegrationAuditService $audit,
        protected IntegrationProviderCatalog $catalog,
    ) {}

    public function connect(IntegrationProvider $provider, array $config, int $userId): IntegrationProvider
    {
        $old = $provider->getOriginal();

        $provider->update([
            'status' => IntegrationProviderStatus::Connected,
            'config' => $config,
            'connected_at' => now(),
            'disconnected_at' => null,
            'last_sync_error' => null,
            'updated_by' => $userId,
        ]);

        $this->logAction($provider, $userId, 'connect', 'success', __('Provider connected.'));
        $this->audit->logChange($provider, 'connected', $old, $provider->getAttributes());

        return $provider->fresh();
    }

    public function disconnect(IntegrationProvider $provider, int $userId): IntegrationProvider
    {
        $old = $provider->getOriginal();

        $provider->update([
            'status' => IntegrationProviderStatus::Disconnected,
            'config' => null,
            'disconnected_at' => now(),
            'updated_by' => $userId,
        ]);

        $this->logAction($provider, $userId, 'disconnect', 'success', __('Provider disconnected.'));
        $this->audit->logChange($provider, 'disconnected', $old, $provider->getAttributes());

        return $provider->fresh();
    }

    public function healthCheck(IntegrationProvider $provider, int $userId): array
    {
        $healthy = $provider->status === IntegrationProviderStatus::Connected && filled($provider->config);

        $this->logAction(
            $provider,
            $userId,
            'health_check',
            $healthy ? 'success' : 'failed',
            $healthy ? __('Provider is healthy.') : __('Provider is not connected or missing configuration.'),
        );

        if (! $healthy) {
            $provider->update(['status' => IntegrationProviderStatus::Error]);
        }

        return [
            'success' => $healthy,
            'message' => $healthy ? __('Health check passed.') : __('Health check failed.'),
        ];
    }

    public function sync(IntegrationProvider $provider, int $userId): IntegrationSyncLog
    {
        $syncLog = IntegrationSyncLog::query()->create([
            'provider_id' => $provider->id,
            'sync_type' => 'full',
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            if ($provider->status !== IntegrationProviderStatus::Connected) {
                throw new \RuntimeException(__('Provider is not connected.'));
            }

            $syncLog->update([
                'status' => 'success',
                'records_synced' => 0,
                'completed_at' => now(),
            ]);

            $provider->update([
                'last_sync_at' => now(),
                'last_sync_error' => null,
            ]);

            $this->logAction($provider, $userId, 'sync', 'success', __('Sync completed.'));
        } catch (\Throwable $e) {
            $syncLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            $provider->update([
                'last_sync_error' => $e->getMessage(),
                'status' => IntegrationProviderStatus::Error,
            ]);

            $this->logAction($provider, $userId, 'sync', 'failed', $e->getMessage());
        }

        return $syncLog->fresh();
    }

    protected function logAction(
        IntegrationProvider $provider,
        int $userId,
        string $action,
        string $status,
        ?string $message = null,
    ): void {
        IntegrationProviderLog::query()->create([
            'provider_id' => $provider->id,
            'user_id' => $userId,
            'action' => $action,
            'status' => $status,
            'message' => $message,
            'created_at' => now(),
        ]);
    }
}

<?php

namespace App\Support\Integrations;

use App\Support\ActivityLogger;
use Illuminate\Database\Eloquent\Model;

class IntegrationAuditService
{
    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public function logChange(Model $model, string $action, array $oldValues = [], array $newValues = []): void
    {
        ActivityLogger::log($action, $model, properties: [
            'old' => IntegrationSecretMask::maskArray($oldValues),
            'new' => IntegrationSecretMask::maskArray($newValues),
        ]);
    }
}

<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Services\Security\SecurityAuditService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log(
        string $action,
        ?Model $model = null,
        ?int $userId = null,
        array $properties = [],
        ?array $before = null,
    ): ActivityLog {
        $log = ActivityLog::query()->create([
            'company_id' => auth()->user()?->company_id ?? self::resolveCompanyId($model),
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'model_type' => $model ? $model::class : null,
            'model_id' => $model?->getKey(),
            'ip_address' => Request::ip(),
            'properties' => $properties ?: null,
            'created_at' => now(),
        ]);

        app(SecurityAuditService::class)->record(
            action: $action,
            subject: $model,
            userId: $userId,
            before: $before,
            after: $properties ?: null,
            metadata: $properties,
        );

        return $log;
    }

    protected static function resolveCompanyId(?Model $model): ?int
    {
        if ($model !== null && isset($model->company_id)) {
            return (int) $model->company_id;
        }

        return null;
    }
}

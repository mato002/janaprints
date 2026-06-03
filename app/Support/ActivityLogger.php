<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log(
        string $action,
        ?Model $model = null,
        ?int $userId = null,
        array $properties = [],
    ): ActivityLog {
        return ActivityLog::query()->create([
            'company_id' => auth()->user()?->company_id,
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'model_type' => $model ? $model::class : null,
            'model_id' => $model?->getKey(),
            'ip_address' => Request::ip(),
            'properties' => $properties ?: null,
            'created_at' => now(),
        ]);
    }
}

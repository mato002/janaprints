<?php

namespace App\Models\Concerns;

use App\Support\ActivityLogger;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(fn ($model) => ActivityLogger::log('created', $model));
        static::updated(fn ($model) => ActivityLogger::log(
            'updated',
            $model,
            null,
            $model->getChanges(),
            $model->getOriginal(),
        ));
        static::deleted(fn ($model) => ActivityLogger::log('deleted', $model));
    }
}

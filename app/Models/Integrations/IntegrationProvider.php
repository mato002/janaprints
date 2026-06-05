<?php

namespace App\Models\Integrations;

use App\Enums\IntegrationProviderStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IntegrationProvider extends Model
{
    use BelongsToTenant, LogsActivity;

    protected $fillable = [
        'company_id', 'category', 'provider_key', 'name', 'status', 'config',
        'last_sync_at', 'last_sync_error', 'connected_at', 'disconnected_at', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => IntegrationProviderStatus::class,
            'config' => 'encrypted:array',
            'last_sync_at' => 'datetime',
            'connected_at' => 'datetime',
            'disconnected_at' => 'datetime',
        ];
    }

    public function resolveRouteBinding($value, $field = null): Model
    {
        $field ??= $this->getRouteKeyName();
        $provider = static::query()->forTenant()->where($field, $value)->first();

        if ($provider === null) {
            throw (new ModelNotFoundException)->setModel(static::class, [$field => $value]);
        }

        return $provider;
    }

    public function logs(): HasMany
    {
        return $this->hasMany(IntegrationProviderLog::class, 'provider_id');
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(IntegrationSyncLog::class, 'provider_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

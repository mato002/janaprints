<?php

namespace App\Models\Integrations;

use App\Enums\IntegrationApiKeyEnvironment;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationApiKey extends Model
{
    use BelongsToTenant, LogsActivity;

    protected $fillable = [
        'company_id', 'name', 'description', 'key', 'secret_prefix', 'secret_hash',
        'environment', 'allowed_ips', 'permissions', 'is_active',
        'last_used_at', 'created_by', 'revoked_at', 'revoked_by',
    ];

    protected $hidden = ['secret_hash'];

    protected function casts(): array
    {
        return [
            'environment' => IntegrationApiKeyEnvironment::class,
            'allowed_ips' => 'array',
            'permissions' => 'array',
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function resolveRouteBinding($value, $field = null): Model
    {
        $field ??= $this->getRouteKeyName();
        $apiKey = static::query()->forTenant()->where($field, $value)->first();

        if ($apiKey === null) {
            throw (new ModelNotFoundException)->setModel(static::class, [$field => $value]);
        }

        return $apiKey;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function revoker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }
}

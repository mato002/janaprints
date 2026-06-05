<?php

namespace App\Models\Integrations;

use App\Enums\IntegrationWebhookStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IntegrationWebhook extends Model
{
    use BelongsToTenant, LogsActivity;

    protected $fillable = [
        'company_id', 'name', 'endpoint_url', 'secret', 'event_types', 'status',
        'retry_count', 'last_delivery_at', 'last_response_code', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'secret' => 'encrypted',
            'event_types' => 'array',
            'status' => IntegrationWebhookStatus::class,
            'last_delivery_at' => 'datetime',
        ];
    }

    public function resolveRouteBinding($value, $field = null): Model
    {
        $field ??= $this->getRouteKeyName();
        $webhook = static::query()->forTenant()->where($field, $value)->first();

        if ($webhook === null) {
            throw (new ModelNotFoundException)->setModel(static::class, [$field => $value]);
        }

        return $webhook;
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(IntegrationWebhookDelivery::class, 'webhook_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

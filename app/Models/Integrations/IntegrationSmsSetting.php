<?php

namespace App\Models\Integrations;

use App\Enums\IntegrationSmsProvider;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationSmsSetting extends Model
{
    use BelongsToTenant, LogsActivity;

    protected $fillable = [
        'company_id', 'provider', 'api_url', 'api_key', 'sender_id', 'username', 'password',
        'callback_url', 'is_active', 'sms_sent_today', 'sms_sent_month', 'failed_count',
        'last_health_check_at', 'health_status', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'provider' => IntegrationSmsProvider::class,
            'api_key' => 'encrypted',
            'password' => 'encrypted',
            'is_active' => 'boolean',
            'last_health_check_at' => 'datetime',
        ];
    }

    public function resolveRouteBinding($value, $field = null): Model
    {
        $field ??= $this->getRouteKeyName();
        $setting = static::query()->forTenant()->where($field, $value)->first();

        if ($setting === null) {
            throw (new ModelNotFoundException)->setModel(static::class, [$field => $value]);
        }

        return $setting;
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

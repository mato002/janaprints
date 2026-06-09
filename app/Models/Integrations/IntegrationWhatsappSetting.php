<?php

namespace App\Models\Integrations;

use App\Enums\IntegrationWhatsappProvider;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationWhatsappSetting extends Model
{
    use BelongsToTenant, LogsActivity;

    protected $fillable = [
        'company_id', 'provider', 'api_url', 'api_key', 'phone_number_id',
        'business_account_id', 'sender_phone', 'username', 'password',
        'webhook_verify_token', 'is_active', 'messages_sent_today', 'messages_sent_month',
        'failed_count', 'last_health_check_at', 'health_status', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'provider' => IntegrationWhatsappProvider::class,
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

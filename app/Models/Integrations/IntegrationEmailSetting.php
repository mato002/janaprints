<?php

namespace App\Models\Integrations;

use App\Enums\IntegrationEmailProvider;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationEmailSetting extends Model
{
    use BelongsToTenant, LogsActivity;

    protected $fillable = [
        'company_id', 'provider', 'from_name', 'from_email', 'reply_to_email', 'is_active',
        'smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username', 'smtp_password',
        'mailgun_domain', 'mailgun_api_key', 'sendgrid_api_key',
        'ses_access_key', 'ses_secret_key', 'ses_region',
        'last_tested_at', 'last_test_success', 'last_successful_send_at',
        'last_failure_at', 'last_failure_message', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'provider' => IntegrationEmailProvider::class,
            'is_active' => 'boolean',
            'smtp_password' => 'encrypted',
            'mailgun_api_key' => 'encrypted',
            'sendgrid_api_key' => 'encrypted',
            'ses_secret_key' => 'encrypted',
            'last_tested_at' => 'datetime',
            'last_test_success' => 'boolean',
            'last_successful_send_at' => 'datetime',
            'last_failure_at' => 'datetime',
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

    public function hasCredential(string $field): bool
    {
        return filled($this->getAttribute($field));
    }
}

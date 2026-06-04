<?php

namespace App\Models\Communications;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'commercial_alerts',
        'production_alerts',
        'accounting_alerts',
        'hr_alerts',
        'system_alerts',
    ];

    protected function casts(): array
    {
        return [
            'commercial_alerts' => 'boolean',
            'production_alerts' => 'boolean',
            'accounting_alerts' => 'boolean',
            'hr_alerts' => 'boolean',
            'system_alerts' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}

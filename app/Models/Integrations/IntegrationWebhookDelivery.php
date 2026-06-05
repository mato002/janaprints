<?php

namespace App\Models\Integrations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationWebhookDelivery extends Model
{
    protected $fillable = [
        'webhook_id', 'event_type', 'payload', 'response_code', 'response_body',
        'status', 'attempt_count', 'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'delivered_at' => 'datetime',
        ];
    }

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(IntegrationWebhook::class, 'webhook_id');
    }
}

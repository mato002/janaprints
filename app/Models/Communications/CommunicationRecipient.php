<?php

namespace App\Models\Communications;

use App\Enums\CommunicationLogStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationRecipient extends Model
{
    protected $fillable = [
        'communication_log_id', 'recipient_type', 'recipient_id',
        'display_name', 'phone', 'email', 'delivery_status', 'read_at',
    ];

    protected function casts(): array
    {
        return [
            'delivery_status' => CommunicationLogStatus::class,
            'read_at' => 'datetime',
        ];
    }

    public function log(): BelongsTo
    {
        return $this->belongsTo(CommunicationLog::class, 'communication_log_id');
    }
}

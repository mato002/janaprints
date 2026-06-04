<?php

namespace App\Models\Communications;

use App\Enums\NotificationReadStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationRead extends Model
{
    protected $fillable = [
        'notification_id',
        'user_id',
        'status',
        'read_at',
        'dismissed_at',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => NotificationReadStatus::class,
            'read_at' => 'datetime',
            'dismissed_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function notification(): BelongsTo
    {
        return $this->belongsTo(ErpNotification::class, 'notification_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

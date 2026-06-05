<?php

namespace App\Models;

use App\Enums\PublicContactMessageStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PublicContactMessage extends Model
{
    public const STATUS_UNREAD = 'unread';

    public const STATUS_READ = 'read';

    public const STATUS_RESPONDED = 'responded';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_SPAM = 'spam';

    protected $fillable = [
        'uuid',
        'name',
        'company',
        'phone',
        'email',
        'subject',
        'message',
        'status',
        'source',
        'assigned_to',
        'admin_notes',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PublicContactMessageStatus::class,
            'responded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PublicContactMessage $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            if (empty($model->status)) {
                $model->status = self::STATUS_UNREAD;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}

<?php

namespace App\Models\Communications;

use App\Enums\CommunicationAttachmentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CommunicationAttachment extends Model
{
    protected $fillable = [
        'communication_log_id', 'attachment_type', 'attachable_type', 'attachable_id', 'label',
    ];

    protected function casts(): array
    {
        return ['attachment_type' => CommunicationAttachmentType::class];
    }

    public function log(): BelongsTo
    {
        return $this->belongsTo(CommunicationLog::class, 'communication_log_id');
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}

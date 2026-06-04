<?php

namespace App\Models\Communications;

use App\Enums\EmailAttachmentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EmailAttachment extends Model
{
    protected $fillable = [
        'email_message_id', 'attachment_type', 'attachable_type', 'attachable_id',
        'label', 'file_path',
    ];

    protected function casts(): array
    {
        return ['attachment_type' => EmailAttachmentType::class];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(EmailMessage::class, 'email_message_id');
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}

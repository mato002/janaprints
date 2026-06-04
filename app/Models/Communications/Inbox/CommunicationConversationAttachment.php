<?php

namespace App\Models\Communications\Inbox;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CommunicationConversationAttachment extends Model
{
    protected $fillable = [
        'communication_conversation_id', 'communication_conversation_message_id',
        'attachment_type', 'attachable_type', 'attachable_id', 'label', 'file_path', 'uploaded_by', 'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(CommunicationConversation::class, 'communication_conversation_id');
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(CommunicationConversationMessage::class, 'communication_conversation_message_id');
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isImage(): bool
    {
        if (in_array($this->attachment_type, ['image', 'artwork', 'proof'], true)) {
            return true;
        }

        return $this->file_path && (bool) preg_match('/\.(jpe?g|png|gif|webp|bmp)$/i', $this->file_path);
    }

    public function previewUrl(): ?string
    {
        return $this->file_path ? asset('storage/'.$this->file_path) : null;
    }
}

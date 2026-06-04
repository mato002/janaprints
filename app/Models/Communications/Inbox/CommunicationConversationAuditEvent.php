<?php

namespace App\Models\Communications\Inbox;

use App\Enums\InboxAuditEventType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationConversationAuditEvent extends Model
{
    protected $fillable = [
        'communication_conversation_id', 'event_type', 'payload', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => InboxAuditEventType::class,
            'payload' => 'array',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(CommunicationConversation::class, 'communication_conversation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

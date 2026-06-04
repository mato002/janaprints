<?php

namespace App\Models\Communications\Inbox;

use App\Enums\InboxMessageChannel;
use App\Enums\InboxMessageStatus;
use App\Models\Communications\CommunicationLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CommunicationConversationMessage extends Model
{
    protected $fillable = [
        'communication_conversation_id', 'company_id', 'channel', 'direction',
        'message_type', 'body', 'status', 'source_type', 'source_id',
        'communication_log_id', 'created_by', 'sent_at', 'delivered_at', 'read_at',
    ];

    protected function casts(): array
    {
        return [
            'channel' => InboxMessageChannel::class,
            'status' => InboxMessageStatus::class,
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(CommunicationConversation::class, 'communication_conversation_id');
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function communicationLog(): BelongsTo
    {
        return $this->belongsTo(CommunicationLog::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

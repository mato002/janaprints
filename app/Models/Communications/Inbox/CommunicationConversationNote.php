<?php

namespace App\Models\Communications\Inbox;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationConversationNote extends Model
{
    protected $fillable = [
        'communication_conversation_id', 'body', 'created_by', 'mentioned_user_ids', 'tags',
    ];

    protected function casts(): array
    {
        return [
            'mentioned_user_ids' => 'array',
            'tags' => 'array',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(CommunicationConversation::class, 'communication_conversation_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

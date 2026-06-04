<?php

namespace App\Models\Communications\Inbox;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationConversationParticipant extends Model
{
    protected $fillable = [
        'communication_conversation_id', 'participant_type', 'participant_id',
        'role', 'display_name', 'phone', 'email',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(CommunicationConversation::class, 'communication_conversation_id');
    }
}

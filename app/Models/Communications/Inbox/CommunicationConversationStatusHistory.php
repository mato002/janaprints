<?php

namespace App\Models\Communications\Inbox;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationConversationStatusHistory extends Model
{
    protected $table = 'communication_conversation_status_history';

    protected $fillable = [
        'communication_conversation_id', 'from_status', 'to_status', 'event', 'payload', 'created_by',
    ];

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(CommunicationConversation::class, 'communication_conversation_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

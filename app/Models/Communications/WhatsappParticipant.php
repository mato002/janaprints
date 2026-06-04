<?php

namespace App\Models\Communications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappParticipant extends Model
{
    protected $fillable = [
        'whatsapp_conversation_id', 'participant_type', 'participant_id',
        'phone_number', 'display_name', 'role',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(WhatsappConversation::class, 'whatsapp_conversation_id');
    }
}

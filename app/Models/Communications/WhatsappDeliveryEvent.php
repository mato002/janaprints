<?php

namespace App\Models\Communications;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappDeliveryEvent extends Model
{
    protected $fillable = [
        'whatsapp_message_id', 'event', 'status_snapshot', 'payload', 'created_by',
    ];

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(WhatsappMessage::class, 'whatsapp_message_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

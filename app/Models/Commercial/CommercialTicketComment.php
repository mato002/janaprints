<?php

namespace App\Models\Commercial;

use App\Enums\CommercialTicketCommentVisibility;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommercialTicketComment extends Model
{
    protected $fillable = [
        'ticket_id', 'user_id', 'comment', 'visibility',
    ];

    protected function casts(): array
    {
        return [
            'visibility' => CommercialTicketCommentVisibility::class,
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(CommercialSupportTicket::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

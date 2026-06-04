<?php

namespace App\Models\Communications\Inbox;

use App\Enums\InboxAssignmentAction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationConversationAssignment extends Model
{
    protected $fillable = [
        'communication_conversation_id', 'from_user_id', 'to_user_id',
        'assigned_department_id', 'assigned_branch_id',
        'action', 'note', 'created_by',
    ];

    protected function casts(): array
    {
        return ['action' => InboxAssignmentAction::class];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(CommunicationConversation::class, 'communication_conversation_id');
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

<?php

namespace App\Models\Commercial;

use App\Enums\CommercialTicketChannel;
use App\Enums\CommercialTicketPriority;
use App\Enums\CommercialTicketStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\Crm\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommercialSupportTicket extends Model
{
    use BelongsToTenant, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'customer_id', 'ticket_number', 'subject', 'description',
        'channel', 'priority', 'status', 'assigned_to', 'created_by',
        'due_at', 'resolved_at', 'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'channel' => CommercialTicketChannel::class,
            'priority' => CommercialTicketPriority::class,
            'status' => CommercialTicketStatus::class,
            'due_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function resolveRouteBinding($value, $field = null): Model
    {
        $field ??= $this->getRouteKeyName();

        $ticket = static::query()->forTenant()->where($field, $value)->first();

        if ($ticket === null) {
            throw (new ModelNotFoundException)->setModel(static::class, [$field => $value]);
        }

        return $ticket;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CommercialTicketComment::class, 'ticket_id');
    }

    public function slaEvents(): HasMany
    {
        return $this->hasMany(CommercialTicketSlaEvent::class, 'ticket_id');
    }

    public function isOverdue(): bool
    {
        return $this->due_at
            && $this->due_at->isPast()
            && ! in_array($this->status, [CommercialTicketStatus::Resolved, CommercialTicketStatus::Closed], true);
    }
}

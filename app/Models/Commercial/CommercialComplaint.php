<?php

namespace App\Models\Commercial;

use App\Enums\CommercialComplaintPriority;
use App\Enums\CommercialComplaintSource;
use App\Enums\CommercialComplaintStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\Crm\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommercialComplaint extends Model
{
    use BelongsToTenant, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'customer_id', 'related_document_type', 'related_document_id',
        'subject', 'description', 'source', 'priority', 'status',
        'assigned_to', 'reported_by', 'resolved_at', 'closed_at', 'resolution_notes',
    ];

    protected function casts(): array
    {
        return [
            'source' => CommercialComplaintSource::class,
            'priority' => CommercialComplaintPriority::class,
            'status' => CommercialComplaintStatus::class,
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function resolveRouteBinding($value, $field = null): Model
    {
        $field ??= $this->getRouteKeyName();

        $complaint = static::query()->forTenant()->where($field, $value)->first();

        if ($complaint === null) {
            throw (new ModelNotFoundException)->setModel(static::class, [$field => $value]);
        }

        return $complaint;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}

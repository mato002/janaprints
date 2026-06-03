<?php

namespace App\Models\Artwork;

use App\Enums\ArtworkPriority;
use App\Enums\ArtworkRequestStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\Crm\Customer;
use App\Models\Sales\Quotation;
use App\Models\User;
use Database\Factories\Artwork\ArtworkRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArtworkRequest extends Model
{
    /** @use HasFactory<ArtworkRequestFactory> */
    use BelongsToTenant, HasFactory, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'customer_id', 'quotation_id', 'request_number',
        'title', 'description', 'requested_by', 'assigned_designer_id', 'priority',
        'status', 'due_date', 'current_version',
    ];

    protected function casts(): array
    {
        return [
            'status' => ArtworkRequestStatus::class,
            'priority' => ArtworkPriority::class,
            'due_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function assignedDesigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_designer_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ArtworkFile::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ArtworkVersion::class)->orderByDesc('version_number');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ArtworkComment::class)->latest();
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(ArtworkApproval::class)->latest('created_at');
    }

    public function currentVersionRecord(): ?ArtworkVersion
    {
        if ($this->current_version < 1) {
            return null;
        }

        return $this->versions()->where('version_number', $this->current_version)->first();
    }

    public function transitionTo(ArtworkRequestStatus $status): void
    {
        if (! $this->status->canTransitionTo($status)) {
            throw new \InvalidArgumentException(
                "Cannot transition from {$this->status->value} to {$status->value}",
            );
        }

        $this->update(['status' => $status]);
    }
}

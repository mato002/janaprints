<?php

namespace App\Models\Production;

use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionPriority;
use App\Enums\ProductionType;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\Crm\Customer;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Database\Factories\Production\ProductionJobCardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionJobCard extends Model
{
    /** @use HasFactory<ProductionJobCardFactory> */
    use BelongsToTenant, HasFactory, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'sales_order_id', 'customer_id', 'quotation_id',
        'artwork_request_id', 'job_card_number', 'production_type', 'priority',
        'planned_start_date', 'planned_end_date', 'actual_start_date', 'actual_end_date',
        'status', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductionJobCardStatus::class,
            'production_type' => ProductionType::class,
            'priority' => ProductionPriority::class,
            'planned_start_date' => 'date',
            'planned_end_date' => 'date',
            'actual_start_date' => 'datetime',
            'actual_end_date' => 'datetime',
        ];
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function artworkRequest(): BelongsTo
    {
        return $this->belongsTo(ArtworkRequest::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function queues(): HasMany
    {
        return $this->hasMany(ProductionQueue::class)->orderBy('queue_position');
    }

    public function operations(): HasMany
    {
        return $this->hasMany(ProductionOperation::class)->latest('started_at');
    }

    public function qualityChecks(): HasMany
    {
        return $this->hasMany(QualityCheck::class)->latest('checked_at');
    }

    public function materialConsumptions(): HasMany
    {
        return $this->hasMany(\App\Models\Inventory\ProductionMaterialConsumption::class);
    }

    public function transitionTo(ProductionJobCardStatus $status): void
    {
        if (! $this->status->canTransitionTo($status)) {
            throw new \InvalidArgumentException(
                "Cannot transition from {$this->status->value} to {$status->value}",
            );
        }

        $this->update(['status' => $status]);
    }

    public function isDelayed(): bool
    {
        if (! $this->planned_end_date) {
            return false;
        }

        return $this->planned_end_date->isPast()
            && ! in_array($this->status, [
                ProductionJobCardStatus::Completed,
                ProductionJobCardStatus::ReadyForDispatch,
                ProductionJobCardStatus::Cancelled,
            ], true);
    }
}

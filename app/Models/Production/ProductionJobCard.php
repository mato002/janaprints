<?php

namespace App\Models\Production;

use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionPriority;
use App\Enums\ProductionType;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\MachineJobAssignment;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerArtwork;
use App\Models\Inventory\InventoryItem;
use App\Models\Procurement\Vendor;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Database\Factories\Production\ProductionJobCardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductionJobCard extends Model
{
    /** @use HasFactory<ProductionJobCardFactory> */
    use BelongsToTenant, HasFactory, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'sales_order_id', 'customer_id', 'quotation_id',
        'artwork_request_id', 'inventory_item_id', 'customer_artwork_id',
        'job_card_number', 'production_type', 'priority',
        'planned_start_date', 'planned_end_date', 'required_date', 'estimated_duration_minutes',
        'actual_start_date', 'actual_end_date',
        'status', 'created_by', 'assigned_machine_asset_id',
        'outsource_vendor_id', 'outsource_issue_date', 'outsource_expected_return',
        'outsource_quoted_cost', 'outsource_actual_cost', 'outsource_notes',
        'outsourced_at', 'returned_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductionJobCardStatus::class,
            'production_type' => ProductionType::class,
            'priority' => ProductionPriority::class,
            'planned_start_date' => 'date',
            'planned_end_date' => 'date',
            'required_date' => 'date',
            'estimated_duration_minutes' => 'integer',
            'actual_start_date' => 'datetime',
            'actual_end_date' => 'datetime',
            'outsource_issue_date' => 'date',
            'outsource_expected_return' => 'date',
            'outsource_quoted_cost' => 'decimal:2',
            'outsource_actual_cost' => 'decimal:2',
            'outsourced_at' => 'datetime',
            'returned_at' => 'datetime',
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

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function customerArtwork(): BelongsTo
    {
        return $this->belongsTo(CustomerArtwork::class);
    }

    public function outsourceVendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'outsource_vendor_id');
    }

    public function routeSteps(): HasMany
    {
        return $this->hasMany(JobCardRouteStep::class)->orderBy('sequence');
    }

    public function serialAllocation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(JobCardSerialAllocation::class);
    }

    public function spoiledSerialRanges(): HasMany
    {
        return $this->hasMany(JobCardSpoiledSerialRange::class);
    }

    public function productionSessions(): HasMany
    {
        return $this->hasMany(ProductionSession::class)->orderByDesc('started_at');
    }

    public function materialRequirements(): HasMany
    {
        return $this->hasMany(ProductionMaterialRequirement::class)->orderBy('inventory_item_id');
    }

    public function materialIssues(): HasMany
    {
        return $this->hasMany(ProductionMaterialIssue::class)->orderByDesc('issued_at');
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

    public function productionOutputs(): HasMany
    {
        return $this->hasMany(ProductionOutput::class)->latest('completed_at');
    }

    public function postedProductionOutputs(): HasMany
    {
        return $this->hasMany(ProductionOutput::class)
            ->where('completion_status', \App\Enums\ProductionOutputStatus::Posted)
            ->latest('completed_at');
    }

    public function deliveryNotes(): HasMany
    {
        return $this->hasMany(\App\Models\Dispatch\DeliveryNote::class, 'production_job_card_id');
    }

    public function fulfilment(): HasOne
    {
        return $this->hasOne(ProductionFulfilment::class, 'production_job_card_id');
    }

    public function costSheet(): HasOne
    {
        return $this->hasOne(JobCostSheet::class, 'production_job_card_id');
    }

    public function productionSpecification(): HasOne
    {
        return $this->hasOne(ProductionSpecification::class);
    }

    public function assignedMachine(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'assigned_machine_asset_id');
    }

    public function machineAssignmentHistory(): HasMany
    {
        return $this->hasMany(MachineJobAssignment::class, 'production_job_card_id')->latest('assigned_at');
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

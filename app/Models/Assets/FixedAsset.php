<?php

namespace App\Models\Assets;

use App\Enums\AssetAcquisitionSource;
use App\Enums\AssetCustodyStatus;
use App\Enums\AssetPhysicalCondition;
use App\Enums\DepreciationMethod;
use App\Enums\FixedAssetStatus;
use App\Models\Accounting\Journal;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\GoodsReceiptItem;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\SupplierBill;
use App\Models\Procurement\Vendor;
use App\Models\Branch;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\WorkCenter;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FixedAsset extends Model
{
    use BelongsToTenant, LogsActivity;

    protected $fillable = [
        'company_id',
        'branch_id',
        'asset_category_id',
        'acquisition_source',
        'vendor_id',
        'purchase_request_id',
        'purchase_order_id',
        'goods_receipt_id',
        'goods_receipt_item_id',
        'supplier_bill_id',
        'capitalization_candidate_id',
        'posted_acquisition_journal_id',
        'asset_number',
        'asset_name',
        'barcode',
        'serial_number',
        'manufacturer',
        'model',
        'acquisition_date',
        'capitalization_date',
        'acquisition_cost',
        'residual_value',
        'useful_life_years',
        'depreciation_method',
        'depreciation_start_date',
        'accumulated_depreciation',
        'net_book_value',
        'last_depreciation_date',
        'is_fully_depreciated',
        'status',
        'assigned_to_user_id',
        'assigned_to_branch_id',
        'assigned_to_employee_id',
        'assigned_to_department_id',
        'assigned_custodian_user_id',
        'current_condition',
        'custody_status',
        'notes',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'acquisition_date' => 'date',
            'capitalization_date' => 'date',
            'acquisition_cost' => 'decimal:2',
            'residual_value' => 'decimal:2',
            'useful_life_years' => 'integer',
            'depreciation_method' => DepreciationMethod::class,
            'depreciation_start_date' => 'date',
            'accumulated_depreciation' => 'decimal:2',
            'net_book_value' => 'decimal:2',
            'last_depreciation_date' => 'date',
            'is_fully_depreciated' => 'boolean',
            'acquisition_source' => AssetAcquisitionSource::class,
            'status' => FixedAssetStatus::class,
            'current_condition' => AssetPhysicalCondition::class,
            'custody_status' => AssetCustodyStatus::class,
            'archived_at' => 'datetime',
        ];
    }

    protected function currentBookValue(): Attribute
    {
        return Attribute::get(fn (): float => $this->netBookValue());
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function assignedBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'assigned_to_branch_id');
    }

    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_to_employee_id');
    }

    public function assignedDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'assigned_to_department_id');
    }

    public function handovers(): HasMany
    {
        return $this->hasMany(AssetHandover::class)->latest('handover_date');
    }

    public function assetReturns(): HasMany
    {
        return $this->hasMany(AssetReturn::class)->latest('return_date');
    }

    public function branchTransfers(): HasMany
    {
        return $this->hasMany(AssetBranchTransfer::class)->latest('requested_at');
    }

    public function conditionHistories(): HasMany
    {
        return $this->hasMany(AssetConditionHistory::class)->latest('recorded_at');
    }

    public function custodyTimelineEntries(): HasMany
    {
        return $this->hasMany(AssetCustodyTimelineEntry::class)->latest('occurred_at');
    }

    public function machineOperatorAssignments(): HasMany
    {
        return $this->hasMany(MachineOperatorAssignment::class)->latest('start_date');
    }

    public function vehicleDriverAssignments(): HasMany
    {
        return $this->hasMany(VehicleDriverAssignment::class, 'vehicle_asset_id')->latest('assigned_date');
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class);
    }

    public function depreciationEntries(): HasMany
    {
        return $this->hasMany(AssetDepreciationEntry::class)->latest('period_date');
    }

    public function writeOffs(): HasMany
    {
        return $this->hasMany(AssetWriteOff::class)->latest('write_off_date');
    }

    public function financeTimelineEntries(): HasMany
    {
        return $this->hasMany(AssetFinanceTimelineEntry::class)->latest('occurred_at');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function goodsReceiptItem(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptItem::class);
    }

    public function supplierBill(): BelongsTo
    {
        return $this->belongsTo(SupplierBill::class);
    }

    public function capitalizationCandidate(): BelongsTo
    {
        return $this->belongsTo(AssetCapitalizationCandidate::class, 'capitalization_candidate_id');
    }

    public function acquisitionJournal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'posted_acquisition_journal_id');
    }

    public function custodian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_custodian_user_id');
    }

    public function warranties(): HasMany
    {
        return $this->hasMany(AssetWarranty::class)->latest('warranty_end');
    }

    public function procurementDocuments(): HasMany
    {
        return $this->hasMany(AssetProcurementDocument::class);
    }

    public function disposal(): HasOne
    {
        return $this->hasOne(AssetDisposal::class)->latestOfMany();
    }

    public function assignmentHistories(): HasMany
    {
        return $this->hasMany(AssetAssignmentHistory::class)->latest('assigned_at');
    }

    public function machineProfile(): HasOne
    {
        return $this->hasOne(MachineProfile::class);
    }

    public function workCenter(): HasOne
    {
        return $this->hasOne(WorkCenter::class);
    }

    public function machineTimelineEntries(): HasMany
    {
        return $this->hasMany(MachineTimelineEntry::class)->latest('occurred_at');
    }

    public function machineJobAssignments(): HasMany
    {
        return $this->hasMany(MachineJobAssignment::class)->latest('assigned_at');
    }

    public function assignedJobCards(): HasMany
    {
        return $this->hasMany(ProductionJobCard::class, 'assigned_machine_asset_id');
    }

    public function maintenanceWorkOrders(): HasMany
    {
        return $this->hasMany(MaintenanceWorkOrder::class)->latest('opened_at');
    }

    public function maintenancePlans(): HasMany
    {
        return $this->hasMany(MaintenancePlan::class);
    }

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class)->latest('logged_at');
    }

    public function downtimeRecords(): HasMany
    {
        return $this->hasMany(AssetDowntimeRecord::class)->latest('start_time');
    }

    public function maintenanceTimelineEntries(): HasMany
    {
        return $this->hasMany(MaintenanceTimelineEntry::class)->latest('occurred_at');
    }

    public function maintenanceIncidents(): HasMany
    {
        return $this->hasMany(MaintenanceIncident::class)->latest('reported_at');
    }

    public function isProductionMachine(): bool
    {
        return $this->relationLoaded('machineProfile')
            ? $this->machineProfile !== null
            : $this->machineProfile()->exists();
    }

    public function netBookValue(): float
    {
        return max(0, (float) $this->acquisition_cost - (float) $this->accumulated_depreciation);
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function scopeNotArchived(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeForBranchContext(Builder $query): Builder
    {
        if (tenant()->branchId()) {
            $query->where('branch_id', tenant()->branchId());
        }

        return $query;
    }
}

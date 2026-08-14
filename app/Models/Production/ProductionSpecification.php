<?php

namespace App\Models\Production;

use App\Enums\PrintInkType;
use App\Enums\ProductionSpecificationApprovalStatus;
use App\Enums\ProductionType;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\HasPublicHash;
use App\Models\Concerns\LogsActivity;
use App\Models\Crm\Customer;
use App\Models\Inventory\InventoryItem;
use App\Models\PrintingIntelligence\PrintInkProfile;
use App\Models\Sales\Quotation;
use App\Models\Sales\QuotationItem;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\User;
use Database\Factories\Production\ProductionSpecificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionSpecification extends Model
{
    /** @use HasFactory<ProductionSpecificationFactory> */
    use BelongsToTenant, HasFactory, HasPublicHash, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'customer_id',
        'sales_order_id',
        'sales_order_item_id',
        'production_job_card_id',
        'quotation_id',
        'quotation_item_id',
        'print_product_template_id',
        'production_type',
        'product_description',
        'quantity',
        'unit',
        'size',
        'finished_size',
        'sheet_size',
        'orientation',
        'paper_inventory_item_id',
        'material_inventory_item_id',
        'ink_type',
        'ink_profile_id',
        'colour_mode',
        'sides',
        'binding_type',
        'finishing_type',
        'lamination',
        'foiling',
        'spot_uv',
        'embossing',
        'debossing',
        'die_cutting',
        'creasing',
        'perforation',
        'numbering_required',
        'eyelets',
        'ups',
        'estimated_sheets',
        'waste_allowance_percent',
        'artwork_reference',
        'artwork_version',
        'production_notes',
        'delivery_notes',
        'approval_status',
        'snapshot_payload',
        'job_sheet_payload',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'production_type' => ProductionType::class,
            'ink_type' => PrintInkType::class,
            'approval_status' => ProductionSpecificationApprovalStatus::class,
            'quantity' => 'decimal:3',
            'waste_allowance_percent' => 'decimal:2',
            'ups' => 'integer',
            'estimated_sheets' => 'integer',
            'lamination' => 'boolean',
            'foiling' => 'boolean',
            'spot_uv' => 'boolean',
            'embossing' => 'boolean',
            'debossing' => 'boolean',
            'die_cutting' => 'boolean',
            'creasing' => 'boolean',
            'perforation' => 'boolean',
            'numbering_required' => 'boolean',
            'eyelets' => 'boolean',
            'snapshot_payload' => 'array',
            'job_sheet_payload' => 'array',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jobSheet(): array
    {
        return is_array($this->job_sheet_payload) ? $this->job_sheet_payload : [];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function salesOrderItem(): BelongsTo
    {
        return $this->belongsTo(SalesOrderItem::class);
    }

    public function productionJobCard(): BelongsTo
    {
        return $this->belongsTo(ProductionJobCard::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function quotationItem(): BelongsTo
    {
        return $this->belongsTo(QuotationItem::class);
    }

    public function printProductTemplate(): BelongsTo
    {
        return $this->belongsTo(PrintProductTemplate::class);
    }

    public function paperInventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'paper_inventory_item_id');
    }

    public function materialInventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'material_inventory_item_id');
    }

    public function inkProfile(): BelongsTo
    {
        return $this->belongsTo(PrintInkProfile::class, 'ink_profile_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

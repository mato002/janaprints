<?php

namespace App\Models\Production;

use App\Enums\PrintProductTemplateCategory;
use App\Enums\ProductionType;
use App\Models\Assets\FixedAsset;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\HasPublicHash;
use App\Models\Concerns\LogsActivity;
use App\Models\Crm\Customer;
use App\Models\Inventory\InventoryItem;
use App\Models\User;
use Database\Factories\Production\PrintProductTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrintProductTemplate extends Model
{
    /** @use HasFactory<PrintProductTemplateFactory> */
    use BelongsToTenant, HasFactory, HasPublicHash, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'name',
        'category',
        'description',
        'is_active',
        'revision_number',
        'customer_id',
        'metadata',
        'production_type',
        'default_paper_inventory_item_id',
        'default_material_inventory_item_id',
        'gsm',
        'default_size',
        'default_finished_size',
        'default_sheet_size',
        'default_orientation',
        'default_colour_mode',
        'number_of_colours',
        'default_sides',
        'default_binding_type',
        'default_finishing_type',
        'default_lamination',
        'default_foiling',
        'default_spot_uv',
        'default_embossing',
        'default_debossing',
        'default_die_cutting',
        'default_creasing',
        'default_perforation',
        'default_numbering_required',
        'default_eyelets',
        'default_waste_allowance_percent',
        'default_ups',
        'default_notes',
        'artwork_required',
        'bleed_required',
        'safe_margin',
        'resolution_recommendation',
        'preferred_work_center_id',
        'preferred_machine_asset_id',
        'preferred_operator_skill',
        'optional_outsource',
        'recommended_qc_checklist_id',
        'recommended_packaging',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'category' => PrintProductTemplateCategory::class,
            'production_type' => ProductionType::class,
            'is_active' => 'boolean',
            'metadata' => 'array',
            'default_lamination' => 'boolean',
            'default_foiling' => 'boolean',
            'default_spot_uv' => 'boolean',
            'default_embossing' => 'boolean',
            'default_debossing' => 'boolean',
            'default_die_cutting' => 'boolean',
            'default_creasing' => 'boolean',
            'default_perforation' => 'boolean',
            'default_numbering_required' => 'boolean',
            'default_eyelets' => 'boolean',
            'artwork_required' => 'boolean',
            'bleed_required' => 'boolean',
            'optional_outsource' => 'boolean',
            'default_waste_allowance_percent' => 'decimal:2',
            'number_of_colours' => 'integer',
            'default_ups' => 'integer',
            'revision_number' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function defaultPaperInventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'default_paper_inventory_item_id');
    }

    public function defaultMaterialInventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'default_material_inventory_item_id');
    }

    public function preferredWorkCenter(): BelongsTo
    {
        return $this->belongsTo(WorkCenter::class, 'preferred_work_center_id');
    }

    public function preferredMachineAsset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'preferred_machine_asset_id');
    }

    public function recommendedQcChecklist(): BelongsTo
    {
        return $this->belongsTo(ProductQcChecklist::class, 'recommended_qc_checklist_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function productionSpecifications(): HasMany
    {
        return $this->hasMany(ProductionSpecification::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

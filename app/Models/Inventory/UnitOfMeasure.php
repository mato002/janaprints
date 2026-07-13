<?php

namespace App\Models\Inventory;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\Inventory\UnitOfMeasureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitOfMeasure extends Model
{
    /** @use HasFactory<UnitOfMeasureFactory> */
    use BelongsToTenant, HasFactory;

    protected bool $tenantScopedToBranch = true;

    protected $table = 'units_of_measure';

    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'name',
        'base_unit_id',
        'conversion_factor',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'conversion_factor' => 'decimal:4',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'unit_of_measure_id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(InventoryCategory::class, 'default_uom_id');
    }

    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(self::class, 'base_unit_id');
    }

    public function derivedUnits(): HasMany
    {
        return $this->hasMany(self::class, 'base_unit_id');
    }

    public function isInUse(): bool
    {
        return $this->items()->exists()
            || $this->categories()->exists()
            || $this->derivedUnits()->exists();
    }

    public function conversionLabel(): string
    {
        if ($this->base_unit_id === null) {
            return __('1 :unit (base unit)', ['unit' => $this->name]);
        }

        $baseName = $this->baseUnit?->name ?? __('base unit');
        $factor = rtrim(rtrim(number_format((float) $this->conversion_factor, 4, '.', ''), '0'), '.');

        return __('1 :unit = :factor :base', [
            'unit' => $this->name,
            'factor' => $factor,
            'base' => $baseName,
        ]);
    }

    public function usageCount(): int
    {
        if (isset($this->items_count, $this->categories_count, $this->derived_units_count)) {
            return $this->items_count + $this->categories_count + $this->derived_units_count;
        }

        return $this->items()->count()
            + $this->categories()->count()
            + $this->derivedUnits()->count();
    }
}

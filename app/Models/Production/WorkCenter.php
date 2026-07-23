<?php

namespace App\Models\Production;

use App\Models\Assets\FixedAsset;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\HasPublicHash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkCenter extends Model
{
    use BelongsToTenant, HasPublicHash;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'fixed_asset_id', 'name', 'code', 'description', 'is_active', 'requires_machine',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'requires_machine' => 'boolean',
        ];
    }

    public function machineAsset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }
}

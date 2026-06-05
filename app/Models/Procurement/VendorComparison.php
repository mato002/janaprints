<?php

namespace App\Models\Procurement;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorComparison extends Model
{
    use BelongsToTenant, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id',
        'branch_id',
        'rfq_id',
        'comparison_date',
        'status',
        'recommended_vendor_id',
        'recommendation_notes',
        'matrix',
        'scoring_weights',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'comparison_date' => 'date',
            'matrix' => 'array',
            'scoring_weights' => 'array',
        ];
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function recommendedVendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'recommended_vendor_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

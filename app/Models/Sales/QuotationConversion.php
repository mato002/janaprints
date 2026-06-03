<?php

namespace App\Models\Sales;

use App\Models\Artwork\ArtworkRequest;
use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationConversion extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    public $timestamps = false;

    protected $fillable = [
        'company_id', 'branch_id', 'quotation_id', 'sales_order_id', 'artwork_request_id',
        'quotation_revision_number', 'artwork_version_number', 'converted_by',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function artworkRequest(): BelongsTo
    {
        return $this->belongsTo(ArtworkRequest::class);
    }

    public function converter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_by');
    }
}

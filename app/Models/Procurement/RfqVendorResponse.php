<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfqVendorResponse extends Model
{
    protected $fillable = [
        'rfq_id',
        'rfq_vendor_id',
        'rfq_item_id',
        'quoted_price',
        'lead_time_days',
        'warranty',
        'delivery_terms',
        'comments',
        'attachment_path',
    ];

    protected function casts(): array
    {
        return [
            'quoted_price' => 'decimal:2',
        ];
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function rfqVendor(): BelongsTo
    {
        return $this->belongsTo(RfqVendor::class);
    }

    public function rfqItem(): BelongsTo
    {
        return $this->belongsTo(RfqItem::class);
    }

    public function lineTotal(): float
    {
        return (float) $this->quoted_price * (float) $this->rfqItem->quantity;
    }
}

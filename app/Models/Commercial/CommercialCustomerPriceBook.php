<?php

namespace App\Models\Commercial;

use App\Enums\CommercialPriceBookStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Crm\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommercialCustomerPriceBook extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id', 'customer_id', 'price_book_id',
        'starts_at', 'ends_at', 'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => CommercialPriceBookStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function priceBook(): BelongsTo
    {
        return $this->belongsTo(CommercialPriceBook::class, 'price_book_id');
    }
}

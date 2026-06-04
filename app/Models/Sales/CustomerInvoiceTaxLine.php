<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerInvoiceTaxLine extends Model
{
    protected $fillable = [
        'customer_invoice_id',
        'tax_code_id',
        'tax_category_id',
        'tax_code',
        'tax_name',
        'tax_rate',
        'taxable_amount',
        'tax_amount',
    ];

    protected function casts(): array
    {
        return [
            'tax_rate' => 'decimal:2',
            'taxable_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(CustomerInvoice::class, 'customer_invoice_id');
    }
}

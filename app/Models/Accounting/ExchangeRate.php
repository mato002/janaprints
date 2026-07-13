<?php

namespace App\Models\Accounting;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeRate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'currency_code',
        'rate_date',
        'rate_to_base',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'rate_date' => 'date',
            'rate_to_base' => 'decimal:8',
        ];
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }
}

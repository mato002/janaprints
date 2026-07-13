<?php

namespace App\Models\Accounting;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'gl_account_id',
        'name',
        'account_number',
        'currency_code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function glAccount(): BelongsTo
    {
        return $this->belongsTo(GlAccount::class, 'gl_account_id');
    }

    public function statements(): HasMany
    {
        return $this->hasMany(BankStatement::class);
    }
}

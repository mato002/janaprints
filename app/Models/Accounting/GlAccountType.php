<?php

namespace App\Models\Accounting;

use App\Enums\GlAccountTypeCode;
use App\Enums\NormalBalance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GlAccountType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'normal_balance',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'code' => GlAccountTypeCode::class,
            'normal_balance' => NormalBalance::class,
            'sort_order' => 'integer',
        ];
    }

    public function groups(): HasMany
    {
        return $this->hasMany(GlAccountGroup::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(GlAccount::class);
    }
}

<?php

namespace App\Models\Hr;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_id',
    'code',
    'name',
    'is_active',
])]
class PayrollGroupDefinition extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}

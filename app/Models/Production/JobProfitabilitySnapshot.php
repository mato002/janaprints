<?php

namespace App\Models\Production;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class JobProfitabilitySnapshot extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'branch_id',
        'snapshot_scope',
        'scope_id',
        'scope_label',
        'period_start',
        'period_end',
        'revenue',
        'total_cost',
        'gross_profit',
        'margin_percent',
        'job_count',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'revenue' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'gross_profit' => 'decimal:2',
            'margin_percent' => 'decimal:2',
        ];
    }
}

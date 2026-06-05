<?php

namespace App\Models\Hr;

use App\Enums\PayrollItemType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['payroll_payslip_id', 'item_type', 'code', 'name', 'amount', 'sort_order'])]
class PayrollPayslipItem extends Model
{
    protected function casts(): array
    {
        return [
            'item_type' => PayrollItemType::class,
            'amount' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function payslip(): BelongsTo
    {
        return $this->belongsTo(PayrollPayslip::class, 'payroll_payslip_id');
    }
}

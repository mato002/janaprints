<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductQcChecklistLine extends Model
{
    protected $fillable = [
        'product_qc_checklist_id', 'label', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(ProductQcChecklist::class, 'product_qc_checklist_id');
    }
}

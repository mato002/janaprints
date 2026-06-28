<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobCardQcSnapshot extends Model
{
    protected $fillable = [
        'production_job_card_id', 'checklist_items', 'snapshotted_at',
    ];

    protected function casts(): array
    {
        return [
            'checklist_items' => 'array',
            'snapshotted_at' => 'datetime',
        ];
    }

    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(ProductionJobCard::class, 'production_job_card_id');
    }
}

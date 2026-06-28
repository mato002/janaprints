<?php

namespace App\Models\Production;

use App\Enums\JobCardRouteStepStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobCardRouteStep extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'production_job_card_id', 'work_center_id',
        'step_name', 'sequence', 'status',
        'started_at', 'completed_at', 'completed_by',
    ];

    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'status' => JobCardRouteStepStatus::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(ProductionJobCard::class, 'production_job_card_id');
    }

    public function workCenter(): BelongsTo
    {
        return $this->belongsTo(WorkCenter::class);
    }

    public function queueEntry(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProductionQueue::class, 'job_card_route_step_id');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}

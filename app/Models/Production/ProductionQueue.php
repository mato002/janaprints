<?php

namespace App\Models\Production;

use App\Enums\ProductionQueueStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\HasPublicHash;
use App\Models\Production\JobCardRouteStep;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionQueue extends Model
{
    use BelongsToTenant, HasPublicHash;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'production_job_card_id', 'job_card_route_step_id',
        'work_center_id', 'queue_position', 'assigned_operator_id', 'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductionQueueStatus::class,
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

    public function routeStep(): BelongsTo
    {
        return $this->belongsTo(JobCardRouteStep::class, 'job_card_route_step_id');
    }

    public function assignedOperator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_operator_id');
    }
}

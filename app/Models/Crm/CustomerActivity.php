<?php

namespace App\Models\Crm;

use App\Enums\ActivityStatus;
use App\Enums\ActivityType;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerActivity extends Model
{
    use BelongsToTenant, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'customer_id', 'lead_id', 'user_id',
        'activity_type', 'status', 'subject', 'description', 'activity_at',
    ];

    protected function casts(): array
    {
        return [
            'activity_type' => ActivityType::class,
            'status' => ActivityStatus::class,
            'activity_at' => 'datetime',
        ];
    }

    public function resolveRouteBinding($value, $field = null): Model
    {
        $field ??= $this->getRouteKeyName();

        $activity = static::query()->forTenant()->where($field, $value)->first();

        if ($activity === null) {
            throw (new ModelNotFoundException)->setModel(static::class, [$field => $value]);
        }

        return $activity;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models\Communications;

use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Models\Company;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ErpNotification extends Model
{
    use LogsActivity;

    protected $table = 'notifications';

    protected $fillable = [
        'company_id',
        'recipient_user_id',
        'type',
        'priority',
        'title',
        'body',
        'action_url',
        'required_permission',
        'subject_type',
        'subject_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => NotificationType::class,
            'priority' => NotificationPriority::class,
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function readState(): HasOne
    {
        return $this->hasOne(NotificationRead::class, 'notification_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForTenant($query)
    {
        if (tenant()->isSuperAdmin && ! tenant()->hasCompany()) {
            return $query;
        }

        if ($companyId = tenant()->companyId()) {
            return $query->where($this->getTable().'.company_id', $companyId);
        }

        return $query->whereRaw('1 = 0');
    }
}

<?php

namespace App\Models;

use App\Enums\UserSessionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSessionRecord extends Model
{
    protected $fillable = [
        'laravel_session_id',
        'user_id',
        'company_id',
        'branch_id',
        'role_snapshot',
        'ip_address',
        'user_agent',
        'device',
        'browser',
        'platform',
        'location',
        'status',
        'login_at',
        'last_activity_at',
        'logged_out_at',
        'revoked_at',
        'revoked_by',
        'revoke_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => UserSessionStatus::class,
            'login_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'logged_out_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function revokedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    public function scopeForTenant(Builder $query): Builder
    {
        if (tenant()->isSuperAdmin && ! tenant()->hasCompany()) {
            return $query;
        }

        if ($companyId = tenant()->companyId()) {
            return $query->where('company_id', $companyId);
        }

        return $query->whereRaw('1 = 0');
    }

    public function isCurrentSession(?string $sessionId): bool
    {
        return $sessionId !== null
            && $this->laravel_session_id !== null
            && hash_equals($this->laravel_session_id, $sessionId);
    }
}

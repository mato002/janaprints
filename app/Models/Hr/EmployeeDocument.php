<?php

namespace App\Models\Hr;

use App\Enums\EmployeeDocumentCategory;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsActivity;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable([
    'company_id',
    'employee_id',
    'category',
    'title',
    'description',
    'current_version',
    'expires_at',
    'renewal_reminder_days',
    'uploaded_by_user_id',
    'is_active',
])]
class EmployeeDocument extends Model
{
    use BelongsToCompany, LogsActivity;

    protected function casts(): array
    {
        return [
            'category' => EmployeeDocumentCategory::class,
            'current_version' => 'integer',
            'expires_at' => 'date',
            'renewal_reminder_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(EmployeeDocumentVersion::class)->orderByDesc('version_number');
    }

    public function currentVersion(): ?EmployeeDocumentVersion
    {
        if ($this->current_version <= 0) {
            return null;
        }

        return $this->versions()->where('version_number', $this->current_version)->first();
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isExpiringSoon(?Carbon $asOf = null): bool
    {
        if ($this->expires_at === null || $this->isExpired()) {
            return false;
        }

        $asOf ??= now();

        return $this->expires_at->lte($asOf->copy()->addDays($this->renewal_reminder_days));
    }

    public function scopeForTenant(Builder $query): Builder
    {
        if (tenant()->isSuperAdmin && ! tenant()->hasCompany()) {
            return $query;
        }

        if ($companyId = tenant()->companyId() ?? auth()->user()?->company_id) {
            return $query->where('company_id', $companyId);
        }

        return $query->whereRaw('1 = 0');
    }

    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query
            ->whereNotNull('expires_at')
            ->where('expires_at', '>=', now()->toDateString())
            ->where('expires_at', '<=', now()->addDays($days)->toDateString());
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now()->toDateString());
    }
}

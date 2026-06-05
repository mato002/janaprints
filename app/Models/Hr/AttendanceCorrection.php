<?php

namespace App\Models\Hr;

use App\Enums\AttendanceCorrectionType;
use App\Enums\AttendanceStatus;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'attendance_record_id',
    'corrected_by_user_id',
    'approved_by_user_id',
    'correction_type',
    'reason',
    'previous_clock_in_at',
    'previous_clock_out_at',
    'previous_status',
    'new_clock_in_at',
    'new_clock_out_at',
    'new_status',
    'approved_at',
])]
class AttendanceCorrection extends Model
{
    use BelongsToCompany, LogsActivity;

    protected function casts(): array
    {
        return [
            'correction_type' => AttendanceCorrectionType::class,
            'previous_clock_in_at' => 'datetime',
            'previous_clock_out_at' => 'datetime',
            'previous_status' => AttendanceStatus::class,
            'new_clock_in_at' => 'datetime',
            'new_clock_out_at' => 'datetime',
            'new_status' => AttendanceStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    public function correctedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corrected_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
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
}

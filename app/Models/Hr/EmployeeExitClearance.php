<?php

namespace App\Models\Hr;

use App\Enums\ClearanceCategory;
use App\Enums\ClearanceStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'employee_exit_id',
    'category',
    'status',
    'cleared_by_user_id',
    'cleared_at',
    'notes',
])]
class EmployeeExitClearance extends Model
{
    protected function casts(): array
    {
        return [
            'category' => ClearanceCategory::class,
            'status' => ClearanceStatus::class,
            'cleared_at' => 'datetime',
        ];
    }

    public function exit(): BelongsTo
    {
        return $this->belongsTo(EmployeeExit::class, 'employee_exit_id');
    }

    public function clearedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cleared_by_user_id');
    }
}

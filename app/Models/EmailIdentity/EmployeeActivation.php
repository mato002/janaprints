<?php

namespace App\Models\EmailIdentity;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'employee_id',
    'user_id',
    'personal_email',
    'corporate_email',
    'intended_role',
    'token_hash',
    'expires_at',
    'last_invitation_sent_at',
    'activated_at',
])]
class EmployeeActivation extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_invitation_sent_at' => 'datetime',
            'activated_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isActivated(): bool
    {
        return $this->activated_at !== null;
    }
}

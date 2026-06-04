<?php

namespace App\Models\Communications;

use App\Enums\EmailAccountStatus;
use App\Enums\EmailProvider;
use App\Enums\EmailVerificationStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Concerns\LogsActivity;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailAccount extends Model
{
    use LogsActivity;

    protected $fillable = [
        'company_id', 'branch_id', 'department_id', 'name', 'from_email', 'from_name',
        'reply_to_email', 'reply_to_name', 'provider', 'smtp_config', 'provider_config',
        'status', 'verification_status', 'is_default', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'provider' => EmailProvider::class,
            'status' => EmailAccountStatus::class,
            'verification_status' => EmailVerificationStatus::class,
            'smtp_config' => 'array',
            'provider_config' => 'array',
            'is_default' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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

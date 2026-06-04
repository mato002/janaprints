<?php

namespace App\Models\Communications;

use App\Enums\SmsCreditTransactionType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Concerns\LogsActivity;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsCreditTransaction extends Model
{
    use LogsActivity;

    protected $fillable = [
        'company_id', 'branch_id', 'department_id', 'transaction_type',
        'sms_campaign_id', 'sms_message_id', 'amount', 'cost_per_sms',
        'monetary_amount', 'balance_after', 'description', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'transaction_type' => SmsCreditTransactionType::class,
            'amount' => 'decimal:4',
            'cost_per_sms' => 'decimal:4',
            'monetary_amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
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

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SmsCampaign::class, 'sms_campaign_id');
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

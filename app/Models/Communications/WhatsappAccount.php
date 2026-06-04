<?php

namespace App\Models\Communications;

use App\Enums\WhatsappAccountStatus;
use App\Enums\WhatsappProvider;
use App\Enums\WhatsappVerificationStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappAccount extends Model
{
    use LogsActivity;

    protected $fillable = [
        'company_id', 'branch_id', 'name', 'phone_number', 'display_name',
        'provider', 'provider_account_ref', 'status', 'verification_status',
        'is_default', 'settings', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'provider' => WhatsappProvider::class,
            'status' => WhatsappAccountStatus::class,
            'verification_status' => WhatsappVerificationStatus::class,
            'is_default' => 'boolean',
            'settings' => 'array',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(WhatsappConversation::class);
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

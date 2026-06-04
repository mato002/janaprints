<?php

namespace App\Models\Communications;

use App\Enums\EmailAutomationEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplate extends Model
{
    protected $fillable = [
        'company_id', 'communication_template_id', 'email_account_id',
        'automation_event', 'provider_template_ref', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'automation_event' => EmailAutomationEvent::class,
            'is_active' => 'boolean',
        ];
    }

    public function communicationTemplate(): BelongsTo
    {
        return $this->belongsTo(CommunicationTemplate::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(EmailAccount::class, 'email_account_id');
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

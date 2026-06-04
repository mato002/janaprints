<?php

namespace App\Models\Communications;

use App\Enums\SmsDeliveryStatus;
use App\Enums\SmsMessageQueueStatus;
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmsMessage extends Model
{
    protected $fillable = [
        'sms_campaign_id', 'sms_recipient_id', 'company_id', 'branch_id',
        'phone_number', 'message_body', 'queue_status', 'delivery_status',
        'segments_count', 'character_count', 'credit_cost', 'attempts',
        'last_attempt_at', 'sent_at', 'delivered_at', 'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'queue_status' => SmsMessageQueueStatus::class,
            'delivery_status' => SmsDeliveryStatus::class,
            'credit_cost' => 'decimal:4',
            'last_attempt_at' => 'datetime',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SmsCampaign::class, 'sms_campaign_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(SmsRecipient::class, 'sms_recipient_id');
    }

    public function communicationLog(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(CommunicationLog::class, 'source');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function providerLogs(): HasMany
    {
        return $this->hasMany(SmsProviderLog::class);
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

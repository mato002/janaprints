<?php

namespace App\Models\Communications;

use App\Enums\SmsCampaignSendMode;
use App\Enums\SmsCampaignStatus;
use App\Enums\SmsRecipientSource;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Concerns\LogsActivity;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmsCampaign extends Model
{
    use LogsActivity;

    protected $fillable = [
        'company_id', 'branch_id', 'department_id', 'campaign_code', 'name', 'description',
        'communication_template_id', 'message_template', 'sample_data', 'send_mode', 'status',
        'recipient_source', 'recipient_filters', 'scheduled_at', 'queued_at', 'started_at',
        'completed_at', 'total_recipients', 'sent_count', 'failed_count', 'character_count',
        'estimated_segments', 'cost_per_sms', 'estimated_cost', 'actual_cost',
        'created_by', 'approved_by', 'sent_by', 'scheduled_by', 'approved_at', 'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'send_mode' => SmsCampaignSendMode::class,
            'status' => SmsCampaignStatus::class,
            'recipient_source' => SmsRecipientSource::class,
            'recipient_filters' => 'array',
            'sample_data' => 'array',
            'scheduled_at' => 'datetime',
            'queued_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'approved_at' => 'datetime',
            'sent_at' => 'datetime',
            'cost_per_sms' => 'decimal:4',
            'estimated_cost' => 'decimal:2',
            'actual_cost' => 'decimal:2',
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

    public function template(): BelongsTo
    {
        return $this->belongsTo(CommunicationTemplate::class, 'communication_template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(SmsRecipient::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SmsMessage::class);
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

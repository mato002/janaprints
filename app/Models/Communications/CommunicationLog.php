<?php

namespace App\Models\Communications;

use App\Enums\CommunicationLogChannel;
use App\Enums\CommunicationLogStatus;
use App\Enums\CommunicationLogType;
use App\Enums\NotificationPriority;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunicationLog extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = [
        'company_id', 'branch_id', 'reference_number', 'channel', 'communication_type',
        'communication_template_id', 'template_code', 'subject', 'message_body', 'status',
        'priority', 'source_type', 'source_id', 'sms_campaign_id', 'sender_user_id',
        'created_by', 'updated_by', 'deleted_by', 'sent_by', 'approved_by',
        'sent_at', 'delivered_at', 'failed_at', 'read_at', 'read_receipt_at',
        'provider_response', 'delivery_response',
    ];

    protected function casts(): array
    {
        return [
            'channel' => CommunicationLogChannel::class,
            'communication_type' => CommunicationLogType::class,
            'status' => CommunicationLogStatus::class,
            'priority' => NotificationPriority::class,
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'failed_at' => 'datetime',
            'read_at' => 'datetime',
            'read_receipt_at' => 'datetime',
            'provider_response' => 'array',
            'delivery_response' => 'array',
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

    public function template(): BelongsTo
    {
        return $this->belongsTo(CommunicationTemplate::class, 'communication_template_id');
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SmsCampaign::class, 'sms_campaign_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sentByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(CommunicationRecipient::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CommunicationAttachment::class);
    }

    public function deliveryEvents(): HasMany
    {
        return $this->hasMany(CommunicationDeliveryEvent::class)->orderByDesc('created_at');
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

<?php

namespace App\Models\Communications;

use App\Enums\EmailDeliveryStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class EmailMessage extends Model
{
    protected $fillable = [
        'company_id', 'branch_id', 'email_campaign_id', 'email_recipient_id',
        'email_account_id', 'to_emails', 'cc_emails', 'bcc_emails', 'subject', 'body',
        'communication_template_id', 'email_template_id', 'status', 'provider_message_ref',
        'provider_response', 'failure_reason', 'queued_at', 'sent_at', 'delivered_at',
        'opened_at', 'clicked_at', 'bounced_at', 'failed_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'to_emails' => 'array',
            'cc_emails' => 'array',
            'bcc_emails' => 'array',
            'status' => EmailDeliveryStatus::class,
            'provider_response' => 'array',
            'queued_at' => 'datetime',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'opened_at' => 'datetime',
            'clicked_at' => 'datetime',
            'bounced_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'email_campaign_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(EmailRecipient::class, 'email_recipient_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(EmailAccount::class, 'email_account_id');
    }

    public function communicationTemplate(): BelongsTo
    {
        return $this->belongsTo(CommunicationTemplate::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(EmailAttachment::class);
    }

    public function deliveryEvents(): HasMany
    {
        return $this->hasMany(EmailDeliveryEvent::class)->orderByDesc('created_at');
    }

    public function communicationLog(): MorphOne
    {
        return $this->morphOne(CommunicationLog::class, 'source');
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

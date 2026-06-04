<?php

namespace App\Models\Communications;

use App\Enums\WhatsappDeliveryStatus;
use App\Enums\WhatsappMessageDirection;
use App\Enums\WhatsappMessageType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class WhatsappMessage extends Model
{
    protected $fillable = [
        'company_id', 'branch_id', 'whatsapp_conversation_id', 'whatsapp_account_id',
        'direction', 'message_type', 'body', 'communication_template_id',
        'whatsapp_template_id', 'status', 'provider_message_ref', 'provider_response',
        'queued_at', 'sent_at', 'delivered_at', 'read_at', 'failed_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'direction' => WhatsappMessageDirection::class,
            'message_type' => WhatsappMessageType::class,
            'status' => WhatsappDeliveryStatus::class,
            'provider_response' => 'array',
            'queued_at' => 'datetime',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'read_at' => 'datetime',
            'failed_at' => 'datetime',
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

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(WhatsappConversation::class, 'whatsapp_conversation_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(WhatsappAccount::class, 'whatsapp_account_id');
    }

    public function communicationTemplate(): BelongsTo
    {
        return $this->belongsTo(CommunicationTemplate::class);
    }

    public function whatsappTemplate(): BelongsTo
    {
        return $this->belongsTo(WhatsappTemplate::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deliveryEvents(): HasMany
    {
        return $this->hasMany(WhatsappDeliveryEvent::class)->orderByDesc('created_at');
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

<?php

namespace App\Models\Communications\Inbox;

use App\Enums\InboxConversationStatus;
use App\Enums\InboxConversationType;
use App\Enums\InboxSlaStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Employee;
use App\Models\Procurement\Vendor;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunicationConversation extends Model
{
    protected $fillable = [
        'company_id', 'branch_id', 'conversation_code', 'conversation_type', 'status', 'priority',
        'customer_id', 'lead_id', 'vendor_id', 'employee_id', 'display_name', 'phone_number', 'email',
        'tags', 'assigned_user_id', 'owner_user_id', 'assigned_department_id', 'assigned_team_label',
        'watcher_user_ids', 'unread_count', 'last_message_preview', 'last_channel', 'preferred_channel',
        'is_escalated', 'escalated_at', 'waiting_since', 'first_response_at', 'last_staff_response_at',
        'last_customer_message_at', 'started_at', 'last_activity_at', 'closed_at', 'resolved_at', 'sla_status',
        'whatsapp_conversation_id', 'quotation_id', 'sales_order_id', 'artwork_request_id',
        'production_job_card_id', 'customer_invoice_id', 'customer_payment_id', 'supplier_bill_id', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'conversation_type' => InboxConversationType::class,
            'status' => InboxConversationStatus::class,
            'watcher_user_ids' => 'array',
            'unread_count' => 'integer',
            'is_escalated' => 'boolean',
            'escalated_at' => 'datetime',
            'waiting_since' => 'datetime',
            'first_response_at' => 'datetime',
            'last_staff_response_at' => 'datetime',
            'last_customer_message_at' => 'datetime',
            'started_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'closed_at' => 'datetime',
            'resolved_at' => 'datetime',
            'sla_status' => InboxSlaStatus::class,
            'tags' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function assignedDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'assigned_department_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(CommunicationConversationParticipant::class);
    }

    public function threadMessages(): HasMany
    {
        return $this->hasMany(CommunicationConversationMessage::class)->orderBy('created_at');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(CommunicationConversationNote::class)->orderByDesc('created_at');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CommunicationConversationAttachment::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(CommunicationConversationAssignment::class)->orderByDesc('created_at');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(CommunicationConversationStatusHistory::class)->orderByDesc('created_at');
    }

    public function auditEvents(): HasMany
    {
        return $this->hasMany(CommunicationConversationAuditEvent::class)->orderByDesc('created_at');
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

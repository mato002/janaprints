<?php

namespace App\Models\Dispatch;

use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Models\Accounting\Journal;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\HasPublicHash;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryNote extends Model
{
    use BelongsToTenant, HasPublicHash, SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'delivery_note_number',
        'customer_id',
        'sales_order_id',
        'production_job_card_id',
        'delivery_date',
        'status',
        'recipient_name',
        'recipient_phone',
        'delivery_address',
        'package_count',
        'package_notes',
        'packaged_by',
        'packaged_at',
        'courier_name',
        'tracking_number',
        'waybill_number',
        'recipient_signature',
        'pod_photo_path',
        'pod_captured_at',
        'dispatch_notes',
        'delivery_notes',
        'dispatched_by',
        'dispatched_at',
        'delivered_by',
        'delivered_at',
        'posted_journal_id',
        'invoice_ready',
        'invoiced_by',
        'invoiced_at',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'status' => DeliveryNoteStatus::class,
            'packaged_at' => 'datetime',
            'dispatched_at' => 'datetime',
            'delivered_at' => 'datetime',
            'pod_captured_at' => 'datetime',
            'invoice_ready' => 'boolean',
            'invoiced_at' => 'datetime',
        ];
    }

    public function activeInvoice(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CustomerInvoice::class, 'delivery_note_id')->latestOfMany()
            ->whereNot('status', \App\Enums\CustomerInvoiceStatus::Cancelled);
    }

    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CustomerInvoice::class, 'delivery_note_id');
    }

    public function invoicer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invoiced_by');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function productionJobCard(): BelongsTo
    {
        return $this->belongsTo(ProductionJobCard::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryNoteItem::class)->orderBy('id');
    }

    public function dispatcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function packager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'packaged_by');
    }

    public function deliverer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function postedJournal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'posted_journal_id');
    }

    /**
     * Phase 3F: delivered notes are eligible for invoicing (no automation yet).
     */
    public function isInvoiceable(): bool
    {
        return $this->status === DeliveryNoteStatus::Delivered
            && $this->invoice_ready;
    }

    public function markInvoiceReady(): void
    {
        if ($this->status === DeliveryNoteStatus::Delivered) {
            $this->forceFill(['invoice_ready' => true])->save();
        }
    }

    public function isPackaged(): bool
    {
        return $this->packaged_at !== null;
    }

    public function workflowStep(): string
    {
        return match ($this->status) {
            DeliveryNoteStatus::Draft => $this->isPackaged() ? 'courier' : 'package',
            DeliveryNoteStatus::Dispatched => 'deliver',
            DeliveryNoteStatus::Delivered => 'complete',
            DeliveryNoteStatus::Cancelled => 'cancelled',
        };
    }
}

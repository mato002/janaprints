<?php

namespace App\Models\Production;

use App\Enums\FulfilmentMethod;
use App\Enums\FulfilmentStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionFulfilment extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'sales_order_id', 'production_job_card_id', 'delivery_note_id',
        'fulfilment_method', 'status', 'invoice_ready',
        'prepared_by', 'prepared_at', 'collection_notes',
        'collected_by_name', 'collector_id_number', 'collector_phone', 'collected_at', 'collection_remarks',
        'recipient_name', 'recipient_phone', 'delivery_address',
        'dispatched_by', 'dispatch_date', 'dispatched_at',
        'received_by', 'delivered_at', 'signature_name', 'delivery_remarks',
    ];

    protected function casts(): array
    {
        return [
            'fulfilment_method' => FulfilmentMethod::class,
            'status' => FulfilmentStatus::class,
            'invoice_ready' => 'boolean',
            'prepared_at' => 'datetime',
            'collected_at' => 'datetime',
            'dispatch_date' => 'date',
            'dispatched_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(ProductionJobCard::class, 'production_job_card_id');
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function preparedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function dispatchedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }
}

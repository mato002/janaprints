<?php

namespace App\Models\Sales;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Enums\QuotationStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\HasPublicHash;
use App\Models\Concerns\LogsActivity;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\User;
use Database\Factories\Sales\QuotationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quotation extends Model
{
    /** @use HasFactory<QuotationFactory> */
    use BelongsToTenant, HasFactory, HasPublicHash, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'customer_id', 'lead_id', 'quotation_number',
        'quotation_date', 'valid_until', 'currency', 'subtotal', 'tax_amount',
        'discount_amount', 'total_amount', 'status', 'revision_number',
        'estimated_material_cost', 'estimated_ink_cost', 'estimated_machine_cost',
        'estimated_labour_cost', 'estimated_overhead_cost', 'estimated_total_cost',
        'estimated_margin_percent', 'recommended_price', 'confidence_score', 'estimation_version',
        'prepared_by', 'approved_by', 'approved_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => QuotationStatus::class,
            'quotation_date' => 'date',
            'valid_until' => 'date',
            'approved_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'estimated_material_cost' => 'decimal:2',
            'estimated_ink_cost' => 'decimal:2',
            'estimated_machine_cost' => 'decimal:2',
            'estimated_labour_cost' => 'decimal:2',
            'estimated_overhead_cost' => 'decimal:2',
            'estimated_total_cost' => 'decimal:2',
            'estimated_margin_percent' => 'decimal:2',
            'recommended_price' => 'decimal:2',
            'confidence_score' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(QuotationRevision::class)->orderByDesc('revision_number');
    }

    public function quotationNotes(): HasMany
    {
        return $this->hasMany(QuotationNote::class);
    }

    public function preparer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function salesOrder(): HasOne
    {
        return $this->hasOne(SalesOrder::class);
    }

    public function artworkRequests(): HasMany
    {
        return $this->hasMany(ArtworkRequest::class);
    }

    public function artworkRequest(): HasOne
    {
        return $this->hasOne(ArtworkRequest::class)->latestOfMany();
    }

    public function scopeAvailableForSalesOrderCreation(Builder $query): Builder
    {
        return $query
            ->where('status', QuotationStatus::Accepted)
            ->whereNotNull('customer_id')
            ->whereDoesntHave('salesOrder');
    }

    public function scopeSelectableForSalesOrderPicker(Builder $query): Builder
    {
        return $query
            ->whereNotNull('customer_id')
            ->whereDoesntHave('salesOrder')
            ->whereNotIn('status', [
                QuotationStatus::Rejected,
                QuotationStatus::Expired,
                QuotationStatus::Converted,
            ]);
    }

    public function salesOrderPickerLabel(): string
    {
        $label = trim($this->quotation_number.' — '.($this->customer?->company_name ?? ''));
        $label .= ' · '.ucfirst(str_replace('_', ' ', $this->status->value));

        if ($this->status === QuotationStatus::Accepted && ! $this->isReadyForSalesOrderConversion()) {
            $label .= ' ('.__('pending artwork approval').')';
        } elseif ($this->status !== QuotationStatus::Accepted) {
            $label .= ' ('.__('accept quotation first').')';
        }

        return $label;
    }

    public function scopeEligibleForSalesOrderConversion(Builder $query): Builder
    {
        return $query
            ->availableForSalesOrderCreation()
            ->whereHas('artworkRequests', function (Builder $artworkQuery) {
                $artworkQuery
                    ->where('status', ArtworkRequestStatus::Approved)
                    ->where('current_version', '>=', 1)
                    ->whereHas('approvals', function (Builder $approvalQuery) {
                        $approvalQuery
                            ->where('decision', ArtworkApprovalDecision::Approved)
                            ->whereExists(function ($versionQuery) {
                                $versionQuery->selectRaw('1')
                                    ->from('artwork_versions')
                                    ->whereColumn('artwork_versions.id', 'artwork_approvals.artwork_version_id')
                                    ->whereColumn('artwork_versions.artwork_request_id', 'artwork_requests.id')
                                    ->whereColumn('artwork_versions.version_number', 'artwork_requests.current_version');
                            });
                    });
            });
    }

    public function isReadyForSalesOrderConversion(): bool
    {
        return static::query()
            ->whereKey($this->id)
            ->eligibleForSalesOrderConversion()
            ->exists();
    }

    public function salesOrderConversionLabel(): string
    {
        $label = trim($this->quotation_number.' — '.($this->customer?->company_name ?? ''));

        if (! $this->isReadyForSalesOrderConversion()) {
            return $label.' ('.__('pending artwork approval').')';
        }

        return $label;
    }

    public function conversion(): HasOne
    {
        return $this->hasOne(QuotationConversion::class);
    }

    public function transitionTo(QuotationStatus $status): void
    {
        if (! $this->status->canTransitionTo($status)) {
            throw new \InvalidArgumentException(
                "Cannot transition from {$this->status->value} to {$status->value}",
            );
        }

        $this->update(['status' => $status]);
    }
}

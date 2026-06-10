<?php

namespace App\Models\Crm;

use App\Casts\FlexibleEnumCast;
use App\Enums\LeadStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\Sales\Quotation;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use BelongsToTenant, LogsActivity;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'lead_source_id', 'assigned_to', 'customer_id',
        'stage_id', 'lead_name', 'company_name', 'phone', 'email', 'estimated_value',
        'probability', 'expected_close_date', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => FlexibleEnumCast::class.':'.LeadStatus::class,
            'estimated_value' => 'decimal:2',
            'expected_close_date' => 'date',
        ];
    }

    public function leadSource(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(LeadStage::class, 'stage_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(LeadFollowUp::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CustomerActivity::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }
}

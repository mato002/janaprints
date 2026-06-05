<?php

namespace App\Models\Operations;

use App\Enums\RetentionPolicyDomain;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetentionPolicy extends Model
{
    protected $fillable = [
        'company_id',
        'domain',
        'archive_after_days',
        'delete_after_days',
        'retention_period_days',
        'legal_hold',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'domain' => RetentionPolicyDomain::class,
            'archive_after_days' => 'integer',
            'delete_after_days' => 'integer',
            'retention_period_days' => 'integer',
            'legal_hold' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function archiveAfterLabel(): string
    {
        return $this->daysLabel($this->archive_after_days);
    }

    public function deleteAfterLabel(): string
    {
        return $this->daysLabel($this->delete_after_days);
    }

    public function retentionPeriodLabel(): string
    {
        return $this->daysLabel($this->retention_period_days);
    }

    protected function daysLabel(?int $days): string
    {
        if ($days === null) {
            return __('Not set');
        }

        return trans_choice(':count day|:count days', $days, ['count' => $days]);
    }
}

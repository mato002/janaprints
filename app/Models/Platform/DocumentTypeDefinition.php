<?php

namespace App\Models\Platform;

use App\Enums\DocumentModule;
use App\Enums\DocumentTypeStatus;
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTypeDefinition extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'name',
        'module',
        'prefix',
        'number_series_key',
        'approval_required',
        'approval_levels',
        'approval_rule_type',
        'retention_period_days',
        'auto_numbering',
        'status',
        'form_key',
        'workflow_json',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'module' => DocumentModule::class,
            'status' => DocumentTypeStatus::class,
            'approval_required' => 'boolean',
            'approval_levels' => 'integer',
            'retention_period_days' => 'integer',
            'auto_numbering' => 'boolean',
            'workflow_json' => 'array',
            'is_system' => 'boolean',
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

    public function isActive(): bool
    {
        return $this->status === DocumentTypeStatus::Active;
    }

    public function retentionLabel(): string
    {
        if ($this->retention_period_days === null) {
            return __('Not set');
        }

        $years = round($this->retention_period_days / 365, 1);

        return $years >= 1
            ? __(':years yrs', ['years' => $years])
            : __(':days days', ['days' => $this->retention_period_days]);
    }
}

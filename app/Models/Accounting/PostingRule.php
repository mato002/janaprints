<?php

namespace App\Models\Accounting;

use App\Enums\PostingModule;
use App\Models\Company;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostingRule extends Model
{
    use LogsActivity;

    protected $fillable = [
        'company_id',
        'event_code',
        'module',
        'posting_template_id',
        'name',
        'description',
        'priority',
        'is_active',
        'auto_post',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'module' => PostingModule::class,
            'is_active' => 'boolean',
            'auto_post' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PostingTemplate::class, 'posting_template_id');
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

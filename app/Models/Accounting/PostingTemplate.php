<?php

namespace App\Models\Accounting;

use App\Enums\PostingModule;
use App\Models\Company;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostingTemplate extends Model
{
    use LogsActivity;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'module',
        'description',
        'is_active',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'module' => PostingModule::class,
            'is_active' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PostingTemplateLine::class)->orderBy('line_number');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(PostingRule::class);
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

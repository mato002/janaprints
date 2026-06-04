<?php

namespace App\Models\Accounting;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostingAccountMapping extends Model
{
    protected $fillable = [
        'company_id',
        'account_key',
        'gl_account_id',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function glAccount(): BelongsTo
    {
        return $this->belongsTo(GlAccount::class);
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

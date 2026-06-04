<?php

namespace App\Models\Accounting;

use App\Enums\GlAccountStatus;
use App\Enums\NormalBalance;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GlAccount extends Model
{
    use LogsActivity;

    protected $fillable = [
        'company_id',
        'branch_id',
        'gl_account_type_id',
        'gl_account_group_id',
        'parent_id',
        'code',
        'name',
        'description',
        'normal_balance',
        'status',
        'is_system',
        'is_postable',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'normal_balance' => NormalBalance::class,
            'status' => GlAccountStatus::class,
            'is_system' => 'boolean',
            'is_postable' => 'boolean',
            'sort_order' => 'integer',
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

    public function accountType(): BelongsTo
    {
        return $this->belongsTo(GlAccountType::class, 'gl_account_type_id');
    }

    public function accountGroup(): BelongsTo
    {
        return $this->belongsTo(GlAccountGroup::class, 'gl_account_group_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('code');
    }

    public function scopeForTenant($query)
    {
        if (tenant()->isSuperAdmin && ! tenant()->hasCompany()) {
            return $query;
        }

        if ($companyId = tenant()->companyId()) {
            $query->where($this->getTable().'.company_id', $companyId);

            if ($branchId = tenant()->branchId()) {
                $query->where(function ($inner) use ($branchId) {
                    $inner->whereNull($this->getTable().'.branch_id')
                        ->orWhere($this->getTable().'.branch_id', $branchId);
                });
            }

            return $query;
        }

        return $query->whereRaw('1 = 0');
    }

    public function isLocked(): bool
    {
        return $this->status === GlAccountStatus::Locked;
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }
}

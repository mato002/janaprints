<?php

namespace App\Models\Communications;

use App\Enums\CommunicationChannel;
use App\Enums\CommunicationTemplateCategory;
use App\Enums\CommunicationTemplateStatus;
use App\Enums\CommunicationTemplateType;
use App\Models\Company;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunicationTemplate extends Model
{
    use LogsActivity;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'category',
        'channel',
        'template_type',
        'subject',
        'body',
        'description',
        'status',
        'version_number',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'channel' => CommunicationChannel::class,
            'category' => CommunicationTemplateCategory::class,
            'template_type' => CommunicationTemplateType::class,
            'status' => CommunicationTemplateStatus::class,
            'version_number' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(CommunicationTemplateVersion::class)->orderByDesc('version_number');
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

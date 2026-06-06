<?php

namespace App\Models\Assets;

use App\Enums\AssetDocumentType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDocument extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'branch_id',
        'fixed_asset_id',
        'document_type',
        'title',
        'original_name',
        'storage_path',
        'disk',
        'mime_type',
        'size',
        'uploaded_by',
        'archived_at',
        'archived_by',
    ];

    protected function casts(): array
    {
        return [
            'document_type' => AssetDocumentType::class,
            'size' => 'integer',
            'archived_at' => 'datetime',
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

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function archiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }
}

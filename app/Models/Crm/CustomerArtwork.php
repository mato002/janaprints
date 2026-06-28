<?php

namespace App\Models\Crm;

use App\Enums\CustomerArtworkStatus;
use App\Enums\CustomerArtworkType;
use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CustomerArtwork extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'customer_id',
        'artwork_name', 'artwork_type', 'version_number', 'is_active_version',
        'file_path', 'file_name', 'mime_type', 'status',
        'uploaded_by', 'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'artwork_type' => CustomerArtworkType::class,
            'status' => CustomerArtworkStatus::class,
            'version_number' => 'integer',
            'is_active_version' => 'boolean',
            'uploaded_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function versionLabel(): string
    {
        return 'v'.$this->version_number;
    }

    public function previewUrl(): ?string
    {
        if (! $this->file_path || ! Storage::disk('local')->exists($this->file_path)) {
            return null;
        }

        return route('admin.crm.customers.artworks.preview', [
            'customer' => $this->customer_id,
            'customerArtwork' => $this->id,
        ]);
    }

    public function isPreviewable(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/')
            || $this->mime_type === 'application/pdf';
    }
}

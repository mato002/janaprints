<?php

namespace App\Models\Artwork;

use App\Models\Concerns\HasPublicHash;
use App\Models\User;
use App\Support\PublicHash\PublicHashResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ArtworkVersion extends Model
{
    use HasPublicHash;

    public $timestamps = false;

    protected $fillable = [
        'artwork_request_id', 'version_number', 'file_path', 'original_name',
        'mime_type', 'size', 'uploaded_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function resolveRouteBinding($value, $field = null): Model
    {
        $version = app(PublicHashResolver::class)->resolve(static::class, $value, $field);

        $parentVisible = ArtworkRequest::query()
            ->forTenant()
            ->whereKey($version->artwork_request_id)
            ->exists();

        if (! $parentVisible) {
            throw (new ModelNotFoundException)->setModel(static::class, [
                (string) config('public_hashes.column', 'public_id') => $value,
            ]);
        }

        return $version;
    }

    public function artworkRequest(): BelongsTo
    {
        return $this->belongsTo(ArtworkRequest::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(ArtworkApproval::class);
    }

    public function isPreviewable(): bool
    {
        if (! $this->file_path || ! Storage::disk('local')->exists($this->file_path)) {
            return false;
        }

        return str_starts_with((string) $this->mime_type, 'image/')
            || $this->mime_type === 'application/pdf';
    }

    public function previewUrl(): string
    {
        $this->loadMissing('artworkRequest');

        return route('admin.artwork.versions.preview', [
            'artworkRequest' => $this->artworkRequest,
            'version' => $this,
        ]);
    }
}

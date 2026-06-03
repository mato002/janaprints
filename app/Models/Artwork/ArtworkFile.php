<?php

namespace App\Models\Artwork;

use App\Enums\ArtworkFileType;
use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtworkFile extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'artwork_request_id', 'uploaded_by',
        'file_type', 'original_name', 'path', 'mime_type', 'size',
    ];

    protected function casts(): array
    {
        return [
            'file_type' => ArtworkFileType::class,
        ];
    }

    public function artworkRequest(): BelongsTo
    {
        return $this->belongsTo(ArtworkRequest::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

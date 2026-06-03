<?php

namespace App\Models\Artwork;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArtworkVersion extends Model
{
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
}

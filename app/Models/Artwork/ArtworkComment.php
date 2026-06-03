<?php

namespace App\Models\Artwork;

use App\Enums\ArtworkCommentType;
use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtworkComment extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    protected $fillable = [
        'company_id', 'branch_id', 'artwork_request_id', 'user_id',
        'comment_type', 'comment',
    ];

    protected function casts(): array
    {
        return [
            'comment_type' => ArtworkCommentType::class,
        ];
    }

    public function artworkRequest(): BelongsTo
    {
        return $this->belongsTo(ArtworkRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models\Artwork;

use App\Enums\ArtworkApprovalDecision;
use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtworkApproval extends Model
{
    use BelongsToTenant;

    protected bool $tenantScopedToBranch = true;

    public $timestamps = false;

    protected $fillable = [
        'company_id', 'branch_id', 'artwork_request_id', 'artwork_version_id',
        'approved_by', 'decision', 'comments',
    ];

    protected function casts(): array
    {
        return [
            'decision' => ArtworkApprovalDecision::class,
            'created_at' => 'datetime',
        ];
    }

    public function artworkRequest(): BelongsTo
    {
        return $this->belongsTo(ArtworkRequest::class);
    }

    public function artworkVersion(): BelongsTo
    {
        return $this->belongsTo(ArtworkVersion::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

<?php

namespace App\Models;

use App\Enums\PublicQuoteRequestPriority;
use App\Enums\PublicQuoteRequestStatus;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PublicQuoteRequest extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_REVIEWING = 'reviewing';

    public const STATUS_QUOTED = 'quoted';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_SPAM = 'spam';

    protected $fillable = [
        'uuid',
        'name',
        'company',
        'phone',
        'email',
        'service_needed',
        'quantity',
        'deadline',
        'message',
        'artwork_path',
        'artwork_original_name',
        'company_id',
        'branch_id',
        'lead_id',
        'quotation_id',
        'artwork_request_id',
        'status',
        'priority',
        'expected_value',
        'probability',
        'target_follow_up_at',
        'source',
        'assigned_to',
        'admin_notes',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PublicQuoteRequestStatus::class,
            'priority' => PublicQuoteRequestPriority::class,
            'expected_value' => 'decimal:2',
            'target_follow_up_at' => 'date',
            'responded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PublicQuoteRequest $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            if (empty($model->status)) {
                $model->status = self::STATUS_PENDING;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Sales\Quotation::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(PublicQuoteRequestNote::class)->latest();
    }

    public function printArtworkAnalyses(): HasMany
    {
        return $this->hasMany(PrintArtworkAnalysis::class)->latest('id');
    }

    public function reference(): string
    {
        return 'QR-'.str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }
}

<?php

namespace App\Models\Accounting;

use App\Enums\PostingAccountResolver;
use App\Enums\PostingAmountSource;
use App\Enums\PostingLineSide;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostingTemplateLine extends Model
{
    protected $fillable = [
        'posting_template_id',
        'line_number',
        'entry_side',
        'account_resolver',
        'gl_account_id',
        'account_key',
        'context_account_field',
        'amount_source',
        'amount_field',
        'line_description',
    ];

    protected function casts(): array
    {
        return [
            'entry_side' => PostingLineSide::class,
            'account_resolver' => PostingAccountResolver::class,
            'amount_source' => PostingAmountSource::class,
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PostingTemplate::class, 'posting_template_id');
    }

    public function glAccount(): BelongsTo
    {
        return $this->belongsTo(GlAccount::class);
    }
}

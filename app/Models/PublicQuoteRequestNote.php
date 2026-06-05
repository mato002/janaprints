<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicQuoteRequestNote extends Model
{
    protected $fillable = [
        'public_quote_request_id',
        'user_id',
        'body',
    ];

    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(PublicQuoteRequest::class, 'public_quote_request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models\Communications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EmailRecipient extends Model
{
    protected $fillable = [
        'email_campaign_id', 'source_type', 'source_id', 'email',
        'display_name', 'variable_data', 'status',
    ];

    protected function casts(): array
    {
        return ['variable_data' => 'array'];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'email_campaign_id');
    }

    public function message(): HasOne
    {
        return $this->hasOne(EmailMessage::class);
    }
}

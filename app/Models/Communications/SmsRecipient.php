<?php

namespace App\Models\Communications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SmsRecipient extends Model
{
    protected $fillable = [
        'sms_campaign_id', 'source_type', 'source_id', 'phone_number',
        'display_name', 'variable_data', 'status',
    ];

    protected function casts(): array
    {
        return ['variable_data' => 'array'];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SmsCampaign::class, 'sms_campaign_id');
    }

    public function message(): HasOne
    {
        return $this->hasOne(SmsMessage::class);
    }
}

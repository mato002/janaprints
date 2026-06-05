<?php

namespace App\Models\Platform;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FormCustomFieldValue extends Model
{
    protected $fillable = [
        'company_id',
        'form_key',
        'entity_type',
        'entity_id',
        'field_key',
        'value',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}

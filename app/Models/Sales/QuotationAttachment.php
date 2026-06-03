<?php

namespace App\Models\Sales;

use App\Enums\QuotationAttachmentType;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationAttachment extends Model
{
    use BelongsToCompany, LogsActivity;

    protected $fillable = [
        'company_id', 'quotation_id', 'uploaded_by', 'attachment_type',
        'original_name', 'path', 'mime_type', 'size',
    ];

    protected function casts(): array
    {
        return ['attachment_type' => QuotationAttachmentType::class];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

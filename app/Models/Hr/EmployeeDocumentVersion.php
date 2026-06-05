<?php

namespace App\Models\Hr;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'employee_document_id',
    'version_number',
    'original_name',
    'path',
    'mime_type',
    'size',
    'uploaded_by_user_id',
    'notes',
])]
class EmployeeDocumentVersion extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'size' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(EmployeeDocument::class, 'employee_document_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}

<?php

namespace App\Models;

use App\Enums\CommercialReportExportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommercialReportExport extends Model
{
    protected $table = 'commercial_report_exports';

    protected $fillable = [
        'company_id',
        'user_id',
        'module',
        'tab',
        'format',
        'scope_payload',
        'status',
        'storage_path',
        'filename',
        'mime_type',
        'row_count',
        'error_message',
        'queued_at',
        'completed_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'scope_payload' => 'array',
            'status' => CommercialReportExportStatus::class,
            'queued_at' => 'datetime',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === CommercialReportExportStatus::Completed
            && $this->storage_path !== null
            && $this->filename !== null
            && ! $this->isExpired();
    }

    public function isExpired(): bool
    {
        return $this->status === CommercialReportExportStatus::Expired
            || ($this->expires_at !== null && $this->expires_at->isPast());
    }

    public function isDownloadable(): bool
    {
        return $this->isCompleted() && ! $this->isExpired();
    }

    public function moduleLabel(): string
    {
        return match ($this->module) {
            'sales' => __('Sales Reports'),
            'quotations' => __('Quotation Reports'),
            'sales_orders' => __('Sales Order Reports'),
            'customers' => __('Customer Reports'),
            'artwork' => __('Artwork Reports'),
            'conversion' => __('Conversion Reports'),
            'inventory' => __('Inventory Reports'),
            default => ucfirst(str_replace('_', ' ', $this->module)),
        };
    }

    public static function mimeTypeForFormat(string $format): string
    {
        return match ($format) {
            'excel' => 'application/vnd.ms-excel',
            'pdf' => 'text/html',
            default => 'text/csv',
        };
    }

    public static function extensionForFormat(string $format): string
    {
        return match ($format) {
            'excel' => 'xls',
            'pdf' => 'html',
            default => 'csv',
        };
    }
}

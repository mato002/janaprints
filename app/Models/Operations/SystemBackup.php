<?php

namespace App\Models\Operations;

use App\Enums\BackupStatus;
use App\Enums\BackupType;
use Illuminate\Database\Eloquent\Model;

class SystemBackup extends Model
{
    protected $fillable = [
        'name',
        'type',
        'relative_path',
        'size_bytes',
        'checksum_sha256',
        'status',
        'backup_created_at',
        'retention_until',
        'verified_at',
        'last_checked_at',
        'restore_readiness',
        'verification_message',
    ];

    protected function casts(): array
    {
        return [
            'type' => BackupType::class,
            'status' => BackupStatus::class,
            'backup_created_at' => 'datetime',
            'retention_until' => 'datetime',
            'verified_at' => 'datetime',
            'last_checked_at' => 'datetime',
            'restore_readiness' => 'array',
            'size_bytes' => 'integer',
        ];
    }

    public function absolutePath(): string
    {
        return rtrim((string) config('platform.backups.root'), DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR
            .str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->relative_path);
    }

    public function isExpired(): bool
    {
        return $this->retention_until !== null && $this->retention_until->isPast();
    }
}

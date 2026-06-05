<?php

namespace App\Enums;

enum BackupType: string
{
    case Database = 'database';
    case File = 'file';
    case Storage = 'storage';

    public function label(): string
    {
        return match ($this) {
            self::Database => __('Database Backups'),
            self::File => __('File Backups'),
            self::Storage => __('Storage Backups'),
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Database => __('Database'),
            self::File => __('File'),
            self::Storage => __('Storage'),
        };
    }
}

<?php

namespace App\Services\Operations;

use App\Enums\BackupStatus;
use App\Enums\BackupType;
use App\Models\Operations\SystemBackup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class BackupManagementService
{
    public function syncCatalog(): int
    {
        $this->ensureDirectories();
        $discovered = 0;

        foreach (BackupType::cases() as $type) {
            $directory = $this->typeDirectory($type);

            if (! is_dir($directory)) {
                continue;
            }

            foreach ($this->discoverFiles($directory) as $file) {
                $relativePath = $this->relativePath($file->getPathname());
                $createdAt = Carbon::createFromTimestamp($file->getMTime());
                $retentionDays = $this->retentionDays($type);
                $retentionUntil = $createdAt->copy()->addDays($retentionDays);
                $status = $retentionUntil->isPast() ? BackupStatus::Expired : BackupStatus::Available;

                SystemBackup::query()->updateOrCreate(
                    ['relative_path' => $relativePath],
                    [
                        'name' => $file->getFilename(),
                        'type' => $type,
                        'size_bytes' => $file->getSize(),
                        'backup_created_at' => $createdAt,
                        'retention_until' => $retentionUntil,
                        'status' => $status,
                    ],
                );

                $discovered++;
            }
        }

        SystemBackup::query()->each(function (SystemBackup $backup) {
            if (! is_file($backup->absolutePath())) {
                $backup->update(['status' => BackupStatus::Missing]);
            } elseif ($backup->isExpired() && $backup->status !== BackupStatus::Missing) {
                $backup->update(['status' => BackupStatus::Expired]);
            }
        });

        return $discovered;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->filteredQuery($filters)
            ->orderByDesc('backup_created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return array<string, int>
     */
    public function summaryMetrics(): array
    {
        $base = SystemBackup::query();

        return [
            'total' => (clone $base)->count(),
            'database' => (clone $base)->where('type', BackupType::Database)->count(),
            'file' => (clone $base)->where('type', BackupType::File)->count(),
            'storage' => (clone $base)->where('type', BackupType::Storage)->count(),
            'verified' => (clone $base)->where('status', BackupStatus::Verified)->count(),
            'expired' => (clone $base)->where('status', BackupStatus::Expired)->count(),
        ];
    }

    public function find(int $id): SystemBackup
    {
        return SystemBackup::query()->findOrFail($id);
    }

    public function verify(SystemBackup $backup): SystemBackup
    {
        $path = $backup->absolutePath();

        if (! is_file($path) || ! is_readable($path)) {
            $backup->update([
                'status' => BackupStatus::Missing,
                'verified_at' => now(),
                'verification_message' => __('Backup file is missing or unreadable.'),
            ]);

            return $backup->fresh();
        }

        $actualSize = filesize($path) ?: 0;

        if ($actualSize <= 0) {
            $backup->update([
                'status' => BackupStatus::Failed,
                'verified_at' => now(),
                'verification_message' => __('Backup file is empty.'),
            ]);

            return $backup->fresh();
        }

        if ($backup->type === BackupType::Database && ! $this->isDatabaseBackupName($backup->name)) {
            $backup->update([
                'status' => BackupStatus::Failed,
                'verified_at' => now(),
                'verification_message' => __('Unexpected database backup extension.'),
            ]);

            return $backup->fresh();
        }

        $checksum = hash_file('sha256', $path) ?: null;

        $backup->update([
            'size_bytes' => $actualSize,
            'checksum_sha256' => $checksum,
            'status' => BackupStatus::Verified,
            'verified_at' => now(),
            'verification_message' => __('Backup integrity verified.'),
        ]);

        return $backup->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    public function restoreReadiness(SystemBackup $backup): array
    {
        $checks = [];
        $path = $backup->absolutePath();
        $ready = true;

        $checks[] = $this->check(__('File exists'), is_file($path));
        $ready = $ready && is_file($path);

        $readable = is_readable($path);
        $checks[] = $this->check(__('File is readable'), $readable);
        $ready = $ready && $readable;

        $notExpired = ! $backup->isExpired();
        $checks[] = $this->check(__('Retention policy allows restore'), $notExpired);
        $ready = $ready && $notExpired;

        $sizeOk = ($backup->size_bytes > 0) && (! is_file($path) || (filesize($path) ?: 0) > 0);
        $checks[] = $this->check(__('Backup size is valid'), $sizeOk);
        $ready = $ready && $sizeOk;

        if ($backup->checksum_sha256 && is_file($path)) {
            $matches = hash_file('sha256', $path) === $backup->checksum_sha256;
            $checks[] = $this->check(__('Checksum matches last verification'), $matches);
            $ready = $ready && $matches;
        } else {
            $checks[] = $this->check(__('Checksum recorded'), false);
            $ready = false;
        }

        $freeSpace = @disk_free_space(dirname($path)) ?: 0;
        $spaceOk = $freeSpace > ($backup->size_bytes * 2);
        $checks[] = $this->check(__('Sufficient free disk space'), $spaceOk);
        $ready = $ready && $spaceOk;

        if ($backup->type === BackupType::Database) {
            $extensionOk = $this->isDatabaseBackupName($backup->name);
            $checks[] = $this->check(__('Database backup format supported'), $extensionOk);
            $ready = $ready && $extensionOk;
        }

        $report = [
            'ready' => $ready,
            'checked_at' => now()->toIso8601String(),
            'checks' => $checks,
            'summary' => $ready
                ? __('Backup is ready for restore planning.')
                : __('Backup is not ready for restore. Resolve failed checks first.'),
        ];

        $backup->update([
            'restore_readiness' => $report,
            'last_checked_at' => now(),
        ]);

        return $report;
    }

    public function deleteExpired(): int
    {
        $expired = SystemBackup::query()
            ->where(function (Builder $query) {
                $query->where('status', BackupStatus::Expired)
                    ->orWhere('retention_until', '<', now());
            })
            ->get();

        $deleted = 0;

        foreach ($expired as $backup) {
            $path = $backup->absolutePath();

            if (is_file($path)) {
                @unlink($path);
            }

            $backup->delete();
            $deleted++;
        }

        return $deleted;
    }

    public function download(SystemBackup $backup): StreamedResponse
    {
        $path = $backup->absolutePath();

        if (! is_file($path) || ! is_readable($path)) {
            throw (new ModelNotFoundException)->setModel(SystemBackup::class, [$backup->id]);
        }

        return response()->streamDownload(function () use ($path) {
            $stream = fopen($path, 'rb');
            if ($stream !== false) {
                fpassthru($stream);
                fclose($stream);
            }
        }, $backup->name, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    public function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = (int) floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);

        return round($bytes / (1024 ** $power), 1).' '.$units[$power];
    }

    /**
     * @return array<string, string>
     */
    public function typeOptions(): array
    {
        $options = ['all' => __('All types')];

        foreach (BackupType::cases() as $type) {
            $options[$type->value] = $type->label();
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    public function statusOptions(): array
    {
        $options = ['all' => __('All statuses')];

        foreach (BackupStatus::cases() as $status) {
            $options[$status->value] = $status->label();
        }

        return $options;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function filteredQuery(array $filters): Builder
    {
        $query = SystemBackup::query();

        if (! empty($filters['type']) && $filters['type'] !== 'all') {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $search = '%'.Str::lower($filters['search']).'%';
            $query->where(function (Builder $inner) use ($search) {
                $inner->whereRaw('LOWER(name) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(relative_path) LIKE ?', [$search]);
            });
        }

        return $query;
    }

    protected function ensureDirectories(): void
    {
        $root = (string) config('platform.backups.root');

        File::ensureDirectoryExists($root);

        foreach (BackupType::cases() as $type) {
            File::ensureDirectoryExists($this->typeDirectory($type));
        }
    }

    protected function typeDirectory(BackupType $type): string
    {
        $subdir = config("platform.backups.directories.{$type->value}", $type->value);

        return rtrim((string) config('platform.backups.root'), DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR
            .$subdir;
    }

    protected function relativePath(string $absolutePath): string
    {
        $root = rtrim(str_replace('\\', '/', (string) config('platform.backups.root')), '/').'/';
        $normalized = str_replace('\\', '/', $absolutePath);

        return ltrim(Str::after($normalized, $root), '/');
    }

    /**
     * @return Collection<int, \SplFileInfo>
     */
    protected function discoverFiles(string $directory): Collection
    {
        $files = collect();

        try {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)) as $file) {
                if ($file->isFile()) {
                    $files->push($file);
                }
            }
        } catch (Throwable) {
            return collect();
        }

        return $files;
    }

    protected function retentionDays(BackupType $type): int
    {
        return (int) config("platform.backups.retention_days.{$type->value}", 30);
    }

    protected function isDatabaseBackupName(string $name): bool
    {
        return (bool) preg_match('/\.(sql|gz|zip|bak)$/i', $name);
    }

    /**
     * @return array{label: string, passed: bool, status: string}
     */
    protected function check(string $label, bool $passed): array
    {
        return [
            'label' => $label,
            'passed' => $passed,
            'status' => $passed ? __('Pass') : __('Fail'),
        ];
    }
}

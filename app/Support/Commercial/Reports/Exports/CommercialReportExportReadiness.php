<?php

namespace App\Support\Commercial\Reports\Exports;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CommercialReportExportReadiness
{
    /**
     * @return list<array{source: string, table: string, ready: bool, notes: string, optional?: bool}>
     */
    public function assess(): array
    {
        return [
            $this->row(__('Export registry'), 'framework', $this->registryReady(), $this->registryNotes()),
            $this->row(__('Export storage'), 'local', $this->storageReady(), $this->storageNotes()),
            $this->row(__('Export queue'), 'exports', $this->queueReady(), $this->queueNotes()),
            $this->row(__('Export records'), 'commercial_report_exports', $this->tableReady(), $this->tableNotes()),
        ];
    }

    public function isReady(): bool
    {
        return collect($this->assess())->every(fn (array $row) => $row['ready']);
    }

    protected function registryReady(): bool
    {
        $required = ['sales', 'quotations', 'sales_orders', 'customers', 'artwork', 'conversion'];

        return collect($required)->every(
            fn (string $module) => in_array($module, CommercialReportExportRegistry::modules(), true),
        );
    }

    protected function registryNotes(): string
    {
        $modules = implode(', ', CommercialReportExportRegistry::modules());

        return __('Registered exporters: :modules', ['modules' => $modules]);
    }

    protected function storageReady(): bool
    {
        try {
            Storage::disk('local')->put('exports/.readiness-check', 'ok');
            Storage::disk('local')->delete('exports/.readiness-check');

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    protected function storageNotes(): string
    {
        return $this->storageReady()
            ? __('Writable local disk for export files')
            : __('Local storage is not writable');
    }

    protected function queueReady(): bool
    {
        $connection = Config::get('queue.default');

        return in_array($connection, ['database', 'redis', 'sqs', 'beanstalkd'], true);
    }

    protected function queueNotes(): string
    {
        $connection = Config::get('queue.default');

        return $connection === 'sync'
            ? __('Queue connection is sync — exports will not run in background')
            : __('Queue connection :connection — worker required on exports queue', ['connection' => $connection]);
    }

    protected function tableReady(): bool
    {
        if (! Schema::hasTable('commercial_report_exports')) {
            return false;
        }

        $required = ['company_id', 'user_id', 'module', 'tab', 'format', 'scope_payload', 'status', 'expires_at'];

        return collect($required)->every(fn (string $col) => Schema::hasColumn('commercial_report_exports', $col));
    }

    protected function tableNotes(): string
    {
        return $this->tableReady()
            ? __('Export history and status tracking available')
            : __('Run migrations to create commercial_report_exports');
    }

    /**
     * @return array{source: string, table: string, ready: bool, notes: string, optional?: bool}
     */
    protected function row(string $source, string $table, bool $ready, string $notes, bool $optional = false): array
    {
        return [
            'source' => $source,
            'table' => $table,
            'ready' => $ready,
            'notes' => $notes,
            'optional' => $optional,
        ];
    }
}

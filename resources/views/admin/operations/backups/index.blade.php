<x-admin-layout
    :title="__('Backups')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('System Operations'), 'url' => route('admin.workspaces.administration.section', ['section' => 'system-operations'])],
        ['label' => __('Backups')],
    ]"
>
    @php
        $bootstrap = [
            'readinessRoute' => route('admin.operations.backups.readiness', ['systemBackup' => '__ID__']),
        ];
    @endphp

    <div
        class="backup-management-center min-w-0"
        x-data="backupManagementWorkspace(@js($bootstrap))"
        @keydown.escape.window="closeDrawer()"
    >
        <x-admin.page-header
            :title="__('Backups')"
            :description="__('Backup governance — database, file, and storage artifacts with verification and retention controls.')"
        >
            <x-slot name="actions">
                @if ($canManage && $metrics['expired'] > 0)
                    <form method="POST" action="{{ route('admin.operations.backups.delete-expired') }}" class="inline">
                        @csrf
                        <button type="submit" class="erp-btn-secondary text-sm">{{ __('Delete Expired') }}</button>
                    </form>
                @endif
            </x-slot>
        </x-admin.page-header>

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            <x-admin.kpi-widget :label="__('Total Backups')" :value="$metrics['total']" icon="archive" />
            <x-admin.kpi-widget :label="__('Database')" :value="$metrics['database']" icon="chip" />
            <x-admin.kpi-widget :label="__('File')" :value="$metrics['file']" icon="document-text" />
            <x-admin.kpi-widget :label="__('Storage')" :value="$metrics['storage']" icon="archive" />
            <x-admin.kpi-widget :label="__('Verified')" :value="$metrics['verified']" icon="badge-check" />
            <x-admin.kpi-widget :label="__('Expired')" :value="$metrics['expired']" icon="exclamation" />
        </div>

        <x-admin.card :padding="false" class="mb-4">
            <x-admin.index-toolbar :action="route('admin.operations.backups.index')" :reset-url="route('admin.operations.backups.index')">
                <select name="type" class="erp-toolbar-select" aria-label="{{ __('Type') }}">
                    @foreach ($typeOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['type'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="search" name="search" value="{{ $filters['search'] }}" class="erp-toolbar-input min-w-[12rem] flex-1" placeholder="{{ __('Search backups…') }}" aria-label="{{ __('Search') }}" data-erp-auto-search>
            </x-admin.index-toolbar>
        </x-admin.card>

        <x-admin.data-table
            :search-placeholder="__('Search backups…')"
            export-filename="backups"
            export-route="admin.administration.exports"
            :export-route-params="['listing' => 'backups']"
            :export-query="request()->query()"
            :format-in-path="true"
        >
            <x-slot name="head">
                <tr>
                    <th scope="col">{{ __('Backup Name') }}</th>
                    <th scope="col">{{ __('Type') }}</th>
                    <th scope="col" class="hidden md:table-cell">{{ __('Size') }}</th>
                    <th scope="col">{{ __('Created') }}</th>
                    <th scope="col" class="hidden lg:table-cell">{{ __('Retention Date') }}</th>
                    <th scope="col">{{ __('Status') }}</th>
                    <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
                </tr>
            </x-slot>
            <x-slot name="body">
                @forelse ($backups as $backup)
                    @php
                        $searchBlob = strtolower(implode(' ', array_filter([
                            $backup->name,
                            $backup->type->value,
                            $backup->status->value,
                            $backup->relative_path,
                        ])));
                    @endphp
                    <tr x-show="rowVisible(@js($searchBlob))">
                        <td class="font-medium text-erp-primary">{{ $backup->name }}</td>
                        <td>{{ $backup->type->shortLabel() }}</td>
                        <td class="hidden md:table-cell tabular-nums">{{ $backupService->formatBytes($backup->size_bytes) }}</td>
                        <td class="text-slate-500">{{ $backup->backup_created_at?->format('M j, Y g:i A') }}</td>
                        <td class="hidden lg:table-cell text-slate-500">{{ $backup->retention_until?->format('M j, Y') ?? '—' }}</td>
                        <td>
                            <x-admin.status-badge :variant="$backup->status->badgeVariant()">
                                {{ $backup->status->label() }}
                            </x-admin.status-badge>
                        </td>
                        <td class="erp-table-actions-col">
                            <div class="flex flex-wrap justify-end gap-1">
                                @if ($canDownload && $backup->status !== \App\Enums\BackupStatus::Missing)
                                    <a
                                        href="{{ route('admin.operations.backups.download', $backup) }}"
                                        class="erp-btn-secondary px-2 py-1 text-xs"
                                    >
                                        {{ __('Download') }}
                                    </a>
                                @endif
                                @if ($canManage)
                                    <form method="POST" action="{{ route('admin.operations.backups.verify', $backup) }}">
                                        @csrf
                                        <button type="submit" class="erp-btn-secondary px-2 py-1 text-xs">{{ __('Verify') }}</button>
                                    </form>
                                    <button
                                        type="button"
                                        class="erp-btn-secondary px-2 py-1 text-xs"
                                        @click="openReadiness({{ $backup->id }})"
                                    >
                                        {{ __('Restore Readiness Check') }}
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-admin.empty-state
                                icon="archive"
                                :title="__('No backups registered')"
                                :description="__('Place backup artifacts in the configured backup directories to begin governance tracking.')"
                            />
                        </td>
                    </tr>
                @endforelse
            </x-slot>
            <x-slot name="footer">
                <x-admin.table-pagination :paginator="$backups" />
            </x-slot>
        </x-admin.data-table>

        <div
            x-cloak
            x-show="drawerOpen"
            class="fixed inset-0 z-40 flex justify-end bg-slate-900/40"
            @click.self="closeDrawer()"
        >
            <div class="flex h-full w-full max-w-xl flex-col bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-erp-border px-5 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-erp-primary">{{ __('Restore Readiness Check') }}</h2>
                        <p class="text-sm text-slate-500" x-text="report?.summary ?? ''"></p>
                    </div>
                    <button type="button" class="erp-btn-secondary text-sm" @click="closeDrawer()">{{ __('Close') }}</button>
                </div>
                <div class="flex-1 overflow-y-auto px-5 py-4">
                    <template x-if="loading">
                        <p class="text-sm text-slate-500">{{ __('Running checks…') }}</p>
                    </template>
                    <template x-if="!loading && report">
                        <div class="space-y-3">
                            <div class="rounded-lg border px-4 py-3" :class="report.ready ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-amber-200 bg-amber-50 text-amber-900'">
                                <p class="font-medium" x-text="report.ready ? @js(__('Ready')) : @js(__('Not Ready'))"></p>
                            </div>
                            <ul class="divide-y divide-erp-border rounded-lg border border-erp-border">
                                <template x-for="check in report.checks ?? []" :key="check.label">
                                    <li class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                                        <span x-text="check.label"></span>
                                        <span
                                            class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                            :class="check.passed ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-red-50 text-red-700 ring-red-600/20'"
                                            x-text="check.status"
                                        ></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

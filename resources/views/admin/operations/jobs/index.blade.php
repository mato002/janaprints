<x-admin-layout
    :title="__('Background Jobs')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('System Operations'), 'url' => route('admin.workspaces.administration.section', ['section' => 'system-operations'])],
        ['label' => __('Background Jobs')],
    ]"
>
    @php
        $bootstrap = [
            'showRoute' => route('admin.operations.jobs.show', ['reference' => '__REF__']),
        ];
    @endphp

    <div
        class="background-jobs-center min-w-0"
        x-data="backgroundJobsWorkspace(@js($bootstrap))"
        @keydown.escape.window="closeDrawer()"
    >
        <x-admin.page-header
            :title="__('Background Jobs')"
            :description="__('Monitor asynchronous queue work — email, SMS, notifications, reports, exports, imports, and accounting jobs.')"
        >
            <x-slot name="actions">
                @if ($canRetry && $metrics['failed'] > 0)
                    <form method="POST" action="{{ route('admin.operations.jobs.retry-failed') }}" class="inline">
                        @csrf
                        <button type="submit" class="erp-btn-secondary text-sm">{{ __('Retry Failed Queue') }}</button>
                    </form>
                @endif
            </x-slot>
        </x-admin.page-header>

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @include('admin.operations.jobs.partials.queue-readiness', ['queueReadiness' => $queueReadiness])

        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            <x-admin.kpi-widget :label="__('Total Jobs')" :value="$metrics['total']" icon="switch-horizontal" />
            <x-admin.kpi-widget :label="__('Pending')" :value="$metrics['pending']" icon="clock" />
            <x-admin.kpi-widget :label="__('Processing')" :value="$metrics['processing']" icon="chip" />
            <x-admin.kpi-widget :label="__('Completed')" :value="$metrics['completed']" icon="badge-check" />
            <x-admin.kpi-widget :label="__('Failed')" :value="$metrics['failed']" icon="exclamation" />
            <x-admin.kpi-widget :label="__('Cancelled')" :value="$metrics['cancelled']" icon="archive" />
        </div>

        <div class="mb-4 flex flex-wrap gap-2">
            @foreach (\App\Enums\BackgroundJobType::cases() as $track)
                @if ($track === \App\Enums\BackgroundJobType::General)
                    @continue
                @endif
                <x-admin.exec-health-chip
                    :label="$track->shortLabel()"
                    :value="collect($jobs->items())->where('type', $track)->count()"
                />
            @endforeach
        </div>

        <x-admin.card :padding="false" class="mb-4">
            <x-admin.index-toolbar :action="route('admin.operations.jobs.index')" :reset-url="route('admin.operations.jobs.index')">
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
                <select name="queue" class="erp-toolbar-select" aria-label="{{ __('Queue') }}">
                    @foreach ($queueOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['queue'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="search" name="search" value="{{ $filters['search'] }}" class="erp-toolbar-input min-w-[12rem] flex-1" placeholder="{{ __('Search jobs…') }}" aria-label="{{ __('Search') }}" data-erp-auto-search>
            </x-admin.index-toolbar>
        </x-admin.card>

        <x-admin.data-table
            :search-placeholder="__('Search jobs…')"
            export-filename="background-jobs"
            export-route="admin.administration.exports"
            :export-route-params="['listing' => 'background-jobs']"
            :export-query="request()->query()"
            :format-in-path="true"
        >
            <x-slot name="head">
                <tr>
                    <th scope="col">{{ __('Job ID') }}</th>
                    <th scope="col">{{ __('Queue') }}</th>
                    <th scope="col">{{ __('Type') }}</th>
                    <th scope="col">{{ __('Status') }}</th>
                    <th scope="col" class="hidden lg:table-cell">{{ __('Started') }}</th>
                    <th scope="col" class="hidden xl:table-cell">{{ __('Completed') }}</th>
                    <th scope="col" class="hidden md:table-cell">{{ __('Duration') }}</th>
                    <th scope="col" class="hidden md:table-cell">{{ __('Attempts') }}</th>
                    <th scope="col" class="hidden lg:table-cell">{{ __('Error') }}</th>
                    <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
                </tr>
            </x-slot>
            <x-slot name="body">
                @forelse ($jobs as $job)
                    @php
                        $searchBlob = strtolower(implode(' ', array_filter([
                            $job['job_id'],
                            $job['queue'],
                            $job['type']->value,
                            $job['status']->value,
                            $job['job_class'],
                            $job['error'] ?? '',
                        ])));
                    @endphp
                    <tr x-show="rowVisible(@js($searchBlob))">
                        <td class="font-mono text-xs">{{ Str::limit($job['job_id'], 18) }}</td>
                        <td>{{ $job['queue'] }}</td>
                        <td>{{ $job['type']->shortLabel() }}</td>
                        <td>
                            <x-admin.status-badge :variant="$job['status']->badgeVariant()">
                                {{ $job['status']->label() }}
                            </x-admin.status-badge>
                        </td>
                        <td class="hidden lg:table-cell text-slate-500">{{ $job['started_label'] }}</td>
                        <td class="hidden xl:table-cell text-slate-500">{{ $job['completed_label'] }}</td>
                        <td class="hidden md:table-cell tabular-nums">{{ $job['duration_label'] }}</td>
                        <td class="hidden md:table-cell tabular-nums">{{ $job['attempts'] }}</td>
                        <td class="hidden lg:table-cell text-xs text-red-700">{{ $job['error'] ?? '—' }}</td>
                        <td class="erp-table-actions-col">
                            <div class="flex flex-wrap justify-end gap-1">
                                @if (! empty($job['error_full']))
                                    <button
                                        type="button"
                                        class="erp-btn-secondary px-2 py-1 text-xs"
                                        @click="openDrawer(@js($job['reference']))"
                                    >
                                        {{ __('View Error') }}
                                    </button>
                                @endif
                                @if ($canRetry && $job['can_retry'])
                                    <form method="POST" action="{{ route('admin.operations.jobs.retry', ['reference' => $job['reference']]) }}">
                                        @csrf
                                        <button type="submit" class="erp-btn-secondary px-2 py-1 text-xs">{{ __('Retry Job') }}</button>
                                    </form>
                                @endif
                                @if ($canCancel && $job['can_cancel'])
                                    <form method="POST" action="{{ route('admin.operations.jobs.cancel', ['reference' => $job['reference']]) }}">
                                        @csrf
                                        <button type="submit" class="erp-btn-secondary px-2 py-1 text-xs">{{ __('Cancel Job') }}</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">
                            <x-admin.empty-state
                                icon="switch-horizontal"
                                :title="__('No background jobs found')"
                                :description="__('Queued and completed jobs will appear here as the platform processes asynchronous work.')"
                            />
                        </td>
                    </tr>
                @endforelse
            </x-slot>
            <x-slot name="footer">
                <x-admin.table-pagination :paginator="$jobs" />
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
                        <h2 class="text-lg font-semibold text-erp-primary">{{ __('Job Error Detail') }}</h2>
                        <p class="text-sm text-slate-500" x-text="detail?.job_id ?? ''"></p>
                    </div>
                    <button type="button" class="erp-btn-secondary text-sm" @click="closeDrawer()">{{ __('Close') }}</button>
                </div>
                <div class="flex-1 overflow-y-auto px-5 py-4">
                    <template x-if="loading">
                        <p class="text-sm text-slate-500">{{ __('Loading…') }}</p>
                    </template>
                    <template x-if="!loading && detail">
                        <div class="space-y-4 text-sm">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Status') }}</p>
                                <p class="mt-1 font-medium" x-text="detail.status_label"></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Job Class') }}</p>
                                <p class="mt-1 font-mono text-xs" x-text="detail.job_class"></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Error') }}</p>
                                <pre class="mt-2 overflow-x-auto rounded-lg bg-slate-950 p-4 text-xs text-slate-100" x-text="detail.error_full || detail.error || '—'"></pre>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

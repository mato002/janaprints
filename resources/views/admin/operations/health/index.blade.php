<x-admin-layout
    :title="__('System Health')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('System Operations'), 'url' => route('admin.workspaces.administration.section', ['section' => 'system-operations'])],
        ['label' => __('System Health')],
    ]"
>
    @php
        $systemStatus = $snapshot['system_status'];
        $kpis = $snapshot['kpis'];
        $database = $snapshot['database'];
        $queue = $snapshot['queue'];
        $storage = $snapshot['storage'];
        $alerts = collect($snapshot['alerts'])->sortByDesc(fn ($alert) => $alert['severity']->rank())->values()->all();

        $warningCount = collect($kpis)->filter(fn ($kpi) => $kpi['status']->value === 'warning')->count();
        $criticalCount = collect($kpis)->filter(fn ($kpi) => $kpi['status']->value === 'critical')->count();

        $summaryItems = [
            [
                'label' => __('Overall'),
                'status' => $systemStatus->value,
                'value' => $systemStatus->label(),
                'detail' => __('Platform status'),
                'icon' => 'chip',
            ],
            [
                'label' => __('Application'),
                'status' => $kpis['application']['status']->value,
                'value' => $kpis['application']['value'],
                'detail' => $kpis['application']['detail'],
                'icon' => 'cog',
            ],
            [
                'label' => __('Database'),
                'status' => $kpis['database']['status']->value,
                'value' => $kpis['database']['value'],
                'detail' => $database['response_time_label'],
                'icon' => 'database',
            ],
            [
                'label' => __('Queue'),
                'status' => $kpis['queue']['status']->value,
                'value' => $kpis['queue']['value'],
                'detail' => trans_choice(':count pending|:count pending', $queue['pending_jobs'], ['count' => number_format($queue['pending_jobs'])]),
                'icon' => 'switch-horizontal',
            ],
            [
                'label' => __('Storage'),
                'status' => $kpis['storage']['status']->value,
                'value' => $storage['usage_percent'].'%',
                'detail' => __(':used used', ['used' => $storage['used_label']]),
                'icon' => 'archive',
            ],
            [
                'label' => __('Backups'),
                'status' => $kpis['backup']['status']->value,
                'value' => $kpis['backup']['value'],
                'detail' => $kpis['backup']['detail'],
                'icon' => 'shield-check',
            ],
        ];

        $storageVariant = match (true) {
            $storage['usage_percent'] >= 80 => 'critical',
            $storage['usage_percent'] >= 60 => 'warning',
            default => 'healthy',
        };

        $workersMissing = $queue['workers_running'] === null && $queue['driver'] !== 'sync';

        $jsSnapshot = [
            'generated_at_formatted' => $snapshot['generated_at_formatted'],
            'system_status' => $systemStatus->value,
            'system_status_label' => $systemStatus->label(),
            'indicator_count' => count($kpis),
            'warning_count' => $warningCount,
            'critical_count' => $criticalCount,
        ];
    @endphp

    <div
        class="system-health-command-center min-w-0 space-y-6"
        x-data="{
            snapshot: @js($jsSnapshot),
            refreshing: false,
            async poll() {
                this.refreshing = true;
                try {
                    const response = await fetch(@js(route('admin.operations.health.snapshot')), {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (! response.ok) return;
                    const data = await response.json();
                    const statuses = Object.values(data.kpis || {}).map(k => k.status);
                    this.snapshot = {
                        generated_at_formatted: data.generated_at_formatted ?? this.snapshot.generated_at_formatted,
                        system_status: data.system_status ?? this.snapshot.system_status,
                        system_status_label: this.statusLabel(data.system_status),
                        indicator_count: Object.keys(data.kpis || {}).length,
                        warning_count: statuses.filter(s => s === 'warning').length,
                        critical_count: statuses.filter(s => s === 'critical').length,
                    };
                } finally {
                    this.refreshing = false;
                }
            },
            statusLabel(status) {
                return { healthy: @js(__('Healthy')), warning: @js(__('Warning')), critical: @js(__('Critical')) }[status] ?? status;
            },
            refreshPage() {
                window.location.reload();
            }
        }"
        x-init="setInterval(() => poll(), 60000)"
    >
        {{-- Command center header + actions --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-widest text-erp-accent">{{ __('Operations Command Center') }}</p>
                <h1 class="mt-1 text-xl font-bold tracking-tight text-erp-primary sm:text-2xl">{{ __('System Health') }}</h1>
                <p class="mt-1 max-w-2xl text-sm text-slate-500">
                    {{ __('Real-time operational monitoring — application services, infrastructure, and alert center.') }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                <span class="inline-flex items-center gap-1.5 rounded-lg border border-erp-border bg-erp-card px-3 py-2 text-xs text-slate-600 shadow-sm">
                    <span class="h-2 w-2 rounded-full" :class="refreshing ? 'animate-pulse bg-erp-accent' : 'bg-emerald-500'"></span>
                    <span>{{ __('Last updated') }}:</span>
                    <span class="font-medium tabular-nums text-erp-primary" x-text="snapshot.generated_at_formatted">{{ $snapshot['generated_at_formatted'] }}</span>
                </span>

                <button
                    type="button"
                    class="erp-btn-secondary inline-flex items-center gap-2 text-sm"
                    title="{{ __('Reload health snapshot') }}"
                    @click="refreshPage()"
                >
                    <x-admin.icon name="refresh" class="h-4 w-4" />
                    {{ __('Refresh Health') }}
                </button>

                @if ($canManage)
                    <form method="POST" action="{{ route('admin.operations.health.refresh') }}" class="inline">
                        @csrf
                        <button
                            type="submit"
                            class="erp-btn-primary inline-flex items-center gap-2 text-sm"
                            title="{{ __('Clear health caches and refresh metrics') }}"
                        >
                            <x-admin.icon name="chip" class="h-4 w-4" />
                            {{ __('Refresh Cache') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if (session('success'))
            <x-admin.alert variant="success">{{ session('success') }}</x-admin.alert>
        @endif

        {{-- Executive status strip --}}
        <x-admin.health.health-summary-strip :items="$summaryItems" />

        {{-- Overall health hero --}}
        <x-admin.card class="overflow-hidden border-erp-border">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ __('System Status') }}</p>
                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        <p class="text-2xl font-bold text-erp-primary">
                            {{ __('Status') }}:
                            <span x-text="snapshot.system_status_label">{{ $systemStatus->label() }}</span>
                        </p>
                        <x-admin.health.health-status-badge :status="$systemStatus->value" />
                    </div>
                    <div class="mt-3 flex flex-wrap gap-4 text-sm text-slate-600">
                        <span>
                            <span class="font-semibold tabular-nums text-erp-primary" x-text="snapshot.indicator_count">{{ count($kpis) }}</span>
                            {{ __('Indicators Monitored') }}
                        </span>
                        <span>
                            <span class="font-semibold tabular-nums text-amber-700" x-text="snapshot.warning_count">{{ $warningCount }}</span>
                            {{ __('Warning') }}
                        </span>
                        <span>
                            <span class="font-semibold tabular-nums text-red-700" x-text="snapshot.critical_count">{{ $criticalCount }}</span>
                            {{ __('Critical') }}
                        </span>
                    </div>
                </div>
            </div>
        </x-admin.card>

        {{-- Infrastructure monitoring panels --}}
        <div class="grid gap-4 xl:grid-cols-12">
            {{-- Database --}}
            <section class="xl:col-span-5">
                <x-admin.card>
                    <x-admin.health.health-section-header
                        :title="__('Database Monitoring')"
                        :subtitle="$database['database_name']"
                        :status="$database['status']->value"
                    />
                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <x-admin.health.health-metric-card
                            :label="__('Connection')"
                            :value="$database['connection_status']"
                            :status="$database['status']->value"
                            icon="database"
                        />
                        <x-admin.health.health-metric-card
                            :label="__('Response')"
                            :value="$database['response_time_label']"
                            icon="clock"
                        />
                        <x-admin.health.health-metric-card
                            :label="__('Tables')"
                            :value="number_format($database['table_count'])"
                            icon="collection"
                        />
                        <x-admin.health.health-metric-card
                            :label="__('Migrations')"
                            :value="$database['migration_status']"
                            :status="$database['pending_migrations'] > 0 ? 'warning' : 'healthy'"
                            icon="switch-horizontal"
                        />
                        <x-admin.health.health-metric-card
                            :label="__('Failed Queries')"
                            :value="number_format($database['failed_queries'])"
                            :status="$database['failed_queries'] > 0 ? 'critical' : 'healthy'"
                            icon="x-circle"
                        />
                        <x-admin.health.health-metric-card
                            :label="__('Slow Queries')"
                            :value="number_format($database['slow_queries'])"
                            :status="$database['slow_queries'] > 0 ? 'warning' : 'healthy'"
                            icon="exclamation"
                        />
                    </div>
                </x-admin.card>
            </section>

            {{-- Queue --}}
            <section class="xl:col-span-4">
                <x-admin.card>
                    <x-admin.health.health-section-header
                        :title="__('Queue Monitoring')"
                        :subtitle="__('Driver: :driver', ['driver' => $queue['driver']])"
                        :status="$queue['status']->value"
                    />

                    @if ($workersMissing)
                        <div class="mt-4">
                            <x-admin.health.health-alert-card :alert="[
                                'type' => 'queue',
                                'severity' => \App\Enums\SystemHealthStatus::Warning,
                                'title' => __('Workers Not Detected'),
                                'message' => __('Queue workers are not reporting as active. Jobs may remain pending until workers are started.'),
                            ]" />
                        </div>
                    @endif

                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <x-admin.health.health-metric-card
                            :label="__('Pending Jobs')"
                            :value="number_format($queue['pending_jobs'])"
                            :status="$queue['pending_jobs'] > 100 ? 'warning' : 'healthy'"
                            icon="clock"
                        />
                        <x-admin.health.health-metric-card
                            :label="__('Failed Jobs')"
                            :value="number_format($queue['failed_jobs'])"
                            :status="$queue['failed_jobs'] > 0 ? 'critical' : 'healthy'"
                            icon="x-circle"
                        />
                        <x-admin.health.health-metric-card
                            :label="__('Longest Waiting')"
                            :value="$queue['longest_waiting_job']"
                            icon="switch-horizontal"
                            class="col-span-2 sm:col-span-1"
                        />
                        <x-admin.health.health-metric-card
                            :label="__('Workers Running')"
                            :value="$queue['workers_label']"
                            :status="$workersMissing ? 'warning' : ($queue['workers_running'] > 0 ? 'healthy' : 'unknown')"
                            icon="chip"
                            class="col-span-2 sm:col-span-1"
                        />
                    </div>
                </x-admin.card>
            </section>

            {{-- Storage --}}
            <section class="xl:col-span-3">
                <x-admin.health.health-progress-card
                    :label="__('Storage Monitoring')"
                    :used-label="$storage['used_label']"
                    :free-label="$storage['free_label']"
                    :percent="$storage['usage_percent']"
                    :status="$storageVariant"
                    :uploads-label="$storage['uploads_label']"
                    :backup-label="$storage['backup_label']"
                />
            </section>
        </div>

        {{-- Secondary KPI grid --}}
        @php
            $secondaryKpis = collect($kpis)->except(['application', 'database', 'queue', 'storage', 'backup']);
        @endphp
        @if ($secondaryKpis->isNotEmpty())
            <div>
                <x-admin.health.health-section-header
                    :title="__('Platform Indicators')"
                    :subtitle="__('Extended service and channel monitoring')"
                />
                <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                    @foreach ($secondaryKpis as $key => $kpi)
                        <div class="space-y-2">
                            <x-admin.kpi-widget
                                :label="$kpi['label']"
                                :value="$kpi['value']"
                                :hint="$kpi['detail']"
                                :icon="match ($key) {
                                    'memory' => 'chip',
                                    'cpu' => 'chip',
                                    'cache' => 'cog',
                                    'session' => 'users',
                                    'mail' => 'inbox',
                                    'sms' => 'device-mobile',
                                    default => 'badge-check',
                                }"
                            />
                            <x-admin.health.health-status-badge :status="$kpi['status']->value" />
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Alert center --}}
        <section>
            <x-admin.card>
                <x-admin.health.health-section-header
                    :title="__('Alert Center')"
                    :subtitle="__('Active operational alerts ranked by severity')"
                    :status="count($alerts) > 0 ? 'warning' : 'healthy'"
                />

                @if ($alerts === [])
                    <x-admin.empty-state
                        icon="badge-check"
                        :title="__('No active alerts')"
                        :description="__('System is operating normally. All monitored systems are within normal operating thresholds.')"
                        class="py-10"
                    />
                @else
                    <div class="mt-4 space-y-3">
                        @foreach ($alerts as $alert)
                            <x-admin.health.health-alert-card :alert="$alert" />
                        @endforeach
                    </div>
                @endif
            </x-admin.card>
        </section>
    </div>
</x-admin-layout>

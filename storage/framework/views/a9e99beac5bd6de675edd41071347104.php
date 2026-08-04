<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('System Health'),'breadcrumbs' => [
        ['label' => __('Administration')],
        ['label' => __('System Operations'), 'url' => route('admin.workspaces.administration.section', ['section' => 'system-operations'])],
        ['label' => __('System Health')],
    ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php
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
    ?>

    <div
        class="system-health-command-center min-w-0 space-y-6"
        x-data="{
            snapshot: <?php echo \Illuminate\Support\Js::from($jsSnapshot)->toHtml() ?>,
            refreshing: false,
            async poll() {
                this.refreshing = true;
                try {
                    const response = await fetch(<?php echo \Illuminate\Support\Js::from(route('admin.operations.health.snapshot'))->toHtml() ?>, {
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
                return { healthy: <?php echo \Illuminate\Support\Js::from(__('Healthy'))->toHtml() ?>, warning: <?php echo \Illuminate\Support\Js::from(__('Warning'))->toHtml() ?>, critical: <?php echo \Illuminate\Support\Js::from(__('Critical'))->toHtml() ?> }[status] ?? status;
            },
            refreshPage() {
                window.location.reload();
            }
        }"
        x-init="setInterval(() => poll(), 60000)"
    >
        
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-widest text-erp-accent"><?php echo e(__('Operations Command Center')); ?></p>
                <h1 class="mt-1 text-xl font-bold tracking-tight text-erp-primary sm:text-2xl"><?php echo e(__('System Health')); ?></h1>
                <p class="mt-1 max-w-2xl text-sm text-slate-500">
                    <?php echo e(__('Real-time operational monitoring — application services, infrastructure, and alert center.')); ?>

                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                <span class="inline-flex items-center gap-1.5 rounded-lg border border-erp-border bg-erp-card px-3 py-2 text-xs text-slate-600 shadow-sm">
                    <span class="h-2 w-2 rounded-full" :class="refreshing ? 'animate-pulse bg-erp-accent' : 'bg-emerald-500'"></span>
                    <span><?php echo e(__('Last updated')); ?>:</span>
                    <span class="font-medium tabular-nums text-erp-primary" x-text="snapshot.generated_at_formatted"><?php echo e($snapshot['generated_at_formatted']); ?></span>
                </span>

                <button
                    type="button"
                    class="erp-btn-secondary inline-flex items-center gap-2 text-sm"
                    title="<?php echo e(__('Reload health snapshot')); ?>"
                    @click="refreshPage()"
                >
                    <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'refresh','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'refresh','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                    <?php echo e(__('Refresh Health')); ?>

                </button>

                <?php if($canManage): ?>
                    <form method="POST" action="<?php echo e(route('admin.operations.health.refresh')); ?>" class="inline">
                        <?php echo csrf_field(); ?>
                        <button
                            type="submit"
                            class="erp-btn-primary inline-flex items-center gap-2 text-sm"
                            title="<?php echo e(__('Clear health caches and refresh metrics')); ?>"
                        >
                            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'chip','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chip','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                            <?php echo e(__('Refresh Cache')); ?>

                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>


        <?php if (isset($component)) { $__componentOriginalfd56403c96f0e282e47dcbadae3249d3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd56403c96f0e282e47dcbadae3249d3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.health.health-summary-strip','data' => ['items' => $summaryItems]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.health.health-summary-strip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summaryItems)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfd56403c96f0e282e47dcbadae3249d3)): ?>
<?php $attributes = $__attributesOriginalfd56403c96f0e282e47dcbadae3249d3; ?>
<?php unset($__attributesOriginalfd56403c96f0e282e47dcbadae3249d3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfd56403c96f0e282e47dcbadae3249d3)): ?>
<?php $component = $__componentOriginalfd56403c96f0e282e47dcbadae3249d3; ?>
<?php unset($__componentOriginalfd56403c96f0e282e47dcbadae3249d3); ?>
<?php endif; ?>

        
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'overflow-hidden border-erp-border']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'overflow-hidden border-erp-border']); ?>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500"><?php echo e(__('System Status')); ?></p>
                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        <p class="text-2xl font-bold text-erp-primary">
                            <?php echo e(__('Status')); ?>:
                            <span x-text="snapshot.system_status_label"><?php echo e($systemStatus->label()); ?></span>
                        </p>
                        <?php if (isset($component)) { $__componentOriginal16682510d2d606e0990dc24bb6455e92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal16682510d2d606e0990dc24bb6455e92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.health.health-status-badge','data' => ['status' => $systemStatus->value]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.health.health-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($systemStatus->value)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal16682510d2d606e0990dc24bb6455e92)): ?>
<?php $attributes = $__attributesOriginal16682510d2d606e0990dc24bb6455e92; ?>
<?php unset($__attributesOriginal16682510d2d606e0990dc24bb6455e92); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal16682510d2d606e0990dc24bb6455e92)): ?>
<?php $component = $__componentOriginal16682510d2d606e0990dc24bb6455e92; ?>
<?php unset($__componentOriginal16682510d2d606e0990dc24bb6455e92); ?>
<?php endif; ?>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-4 text-sm text-slate-600">
                        <span>
                            <span class="font-semibold tabular-nums text-erp-primary" x-text="snapshot.indicator_count"><?php echo e(count($kpis)); ?></span>
                            <?php echo e(__('Indicators Monitored')); ?>

                        </span>
                        <span>
                            <span class="font-semibold tabular-nums text-amber-700" x-text="snapshot.warning_count"><?php echo e($warningCount); ?></span>
                            <?php echo e(__('Warning')); ?>

                        </span>
                        <span>
                            <span class="font-semibold tabular-nums text-red-700" x-text="snapshot.critical_count"><?php echo e($criticalCount); ?></span>
                            <?php echo e(__('Critical')); ?>

                        </span>
                    </div>
                </div>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>

        
        <div class="grid gap-4 xl:grid-cols-12">
            
            <section class="xl:col-span-5">
                <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    <?php if (isset($component)) { $__componentOriginal69aa9ebf9e46f2dd640de69819b8ffdc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69aa9ebf9e46f2dd640de69819b8ffdc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.health.health-section-header','data' => ['title' => __('Database Monitoring'),'subtitle' => $database['database_name'],'status' => $database['status']->value]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.health.health-section-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Database Monitoring')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($database['database_name']),'status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($database['status']->value)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69aa9ebf9e46f2dd640de69819b8ffdc)): ?>
<?php $attributes = $__attributesOriginal69aa9ebf9e46f2dd640de69819b8ffdc; ?>
<?php unset($__attributesOriginal69aa9ebf9e46f2dd640de69819b8ffdc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69aa9ebf9e46f2dd640de69819b8ffdc)): ?>
<?php $component = $__componentOriginal69aa9ebf9e46f2dd640de69819b8ffdc; ?>
<?php unset($__componentOriginal69aa9ebf9e46f2dd640de69819b8ffdc); ?>
<?php endif; ?>
                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <?php if (isset($component)) { $__componentOriginal72995217e797c5272673fc06f3c141db = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72995217e797c5272673fc06f3c141db = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.health.health-metric-card','data' => ['label' => __('Connection'),'value' => $database['connection_status'],'status' => $database['status']->value,'icon' => 'database']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.health.health-metric-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Connection')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($database['connection_status']),'status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($database['status']->value),'icon' => 'database']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72995217e797c5272673fc06f3c141db)): ?>
<?php $attributes = $__attributesOriginal72995217e797c5272673fc06f3c141db; ?>
<?php unset($__attributesOriginal72995217e797c5272673fc06f3c141db); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72995217e797c5272673fc06f3c141db)): ?>
<?php $component = $__componentOriginal72995217e797c5272673fc06f3c141db; ?>
<?php unset($__componentOriginal72995217e797c5272673fc06f3c141db); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal72995217e797c5272673fc06f3c141db = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72995217e797c5272673fc06f3c141db = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.health.health-metric-card','data' => ['label' => __('Response'),'value' => $database['response_time_label'],'icon' => 'clock']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.health.health-metric-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Response')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($database['response_time_label']),'icon' => 'clock']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72995217e797c5272673fc06f3c141db)): ?>
<?php $attributes = $__attributesOriginal72995217e797c5272673fc06f3c141db; ?>
<?php unset($__attributesOriginal72995217e797c5272673fc06f3c141db); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72995217e797c5272673fc06f3c141db)): ?>
<?php $component = $__componentOriginal72995217e797c5272673fc06f3c141db; ?>
<?php unset($__componentOriginal72995217e797c5272673fc06f3c141db); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal72995217e797c5272673fc06f3c141db = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72995217e797c5272673fc06f3c141db = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.health.health-metric-card','data' => ['label' => __('Tables'),'value' => number_format($database['table_count']),'icon' => 'collection']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.health.health-metric-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Tables')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($database['table_count'])),'icon' => 'collection']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72995217e797c5272673fc06f3c141db)): ?>
<?php $attributes = $__attributesOriginal72995217e797c5272673fc06f3c141db; ?>
<?php unset($__attributesOriginal72995217e797c5272673fc06f3c141db); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72995217e797c5272673fc06f3c141db)): ?>
<?php $component = $__componentOriginal72995217e797c5272673fc06f3c141db; ?>
<?php unset($__componentOriginal72995217e797c5272673fc06f3c141db); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal72995217e797c5272673fc06f3c141db = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72995217e797c5272673fc06f3c141db = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.health.health-metric-card','data' => ['label' => __('Migrations'),'value' => $database['migration_status'],'status' => $database['pending_migrations'] > 0 ? 'warning' : 'healthy','icon' => 'switch-horizontal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.health.health-metric-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Migrations')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($database['migration_status']),'status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($database['pending_migrations'] > 0 ? 'warning' : 'healthy'),'icon' => 'switch-horizontal']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72995217e797c5272673fc06f3c141db)): ?>
<?php $attributes = $__attributesOriginal72995217e797c5272673fc06f3c141db; ?>
<?php unset($__attributesOriginal72995217e797c5272673fc06f3c141db); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72995217e797c5272673fc06f3c141db)): ?>
<?php $component = $__componentOriginal72995217e797c5272673fc06f3c141db; ?>
<?php unset($__componentOriginal72995217e797c5272673fc06f3c141db); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal72995217e797c5272673fc06f3c141db = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72995217e797c5272673fc06f3c141db = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.health.health-metric-card','data' => ['label' => __('Failed Queries'),'value' => number_format($database['failed_queries']),'status' => $database['failed_queries'] > 0 ? 'critical' : 'healthy','icon' => 'x-circle']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.health.health-metric-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Failed Queries')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($database['failed_queries'])),'status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($database['failed_queries'] > 0 ? 'critical' : 'healthy'),'icon' => 'x-circle']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72995217e797c5272673fc06f3c141db)): ?>
<?php $attributes = $__attributesOriginal72995217e797c5272673fc06f3c141db; ?>
<?php unset($__attributesOriginal72995217e797c5272673fc06f3c141db); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72995217e797c5272673fc06f3c141db)): ?>
<?php $component = $__componentOriginal72995217e797c5272673fc06f3c141db; ?>
<?php unset($__componentOriginal72995217e797c5272673fc06f3c141db); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal72995217e797c5272673fc06f3c141db = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72995217e797c5272673fc06f3c141db = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.health.health-metric-card','data' => ['label' => __('Slow Queries'),'value' => number_format($database['slow_queries']),'status' => $database['slow_queries'] > 0 ? 'warning' : 'healthy','icon' => 'exclamation']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.health.health-metric-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Slow Queries')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($database['slow_queries'])),'status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($database['slow_queries'] > 0 ? 'warning' : 'healthy'),'icon' => 'exclamation']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72995217e797c5272673fc06f3c141db)): ?>
<?php $attributes = $__attributesOriginal72995217e797c5272673fc06f3c141db; ?>
<?php unset($__attributesOriginal72995217e797c5272673fc06f3c141db); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72995217e797c5272673fc06f3c141db)): ?>
<?php $component = $__componentOriginal72995217e797c5272673fc06f3c141db; ?>
<?php unset($__componentOriginal72995217e797c5272673fc06f3c141db); ?>
<?php endif; ?>
                    </div>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
            </section>

            
            <section class="xl:col-span-4">
                <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    <?php if (isset($component)) { $__componentOriginal69aa9ebf9e46f2dd640de69819b8ffdc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69aa9ebf9e46f2dd640de69819b8ffdc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.health.health-section-header','data' => ['title' => __('Queue Monitoring'),'subtitle' => __('Driver: :driver', ['driver' => $queue['driver']]),'status' => $queue['status']->value]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.health.health-section-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Queue Monitoring')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Driver: :driver', ['driver' => $queue['driver']])),'status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($queue['status']->value)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69aa9ebf9e46f2dd640de69819b8ffdc)): ?>
<?php $attributes = $__attributesOriginal69aa9ebf9e46f2dd640de69819b8ffdc; ?>
<?php unset($__attributesOriginal69aa9ebf9e46f2dd640de69819b8ffdc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69aa9ebf9e46f2dd640de69819b8ffdc)): ?>
<?php $component = $__componentOriginal69aa9ebf9e46f2dd640de69819b8ffdc; ?>
<?php unset($__componentOriginal69aa9ebf9e46f2dd640de69819b8ffdc); ?>
<?php endif; ?>

                    <?php if($workersMissing): ?>
                        <div class="mt-4">
                            <?php if (isset($component)) { $__componentOriginalf25f221051308276747dbbd92521e747 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf25f221051308276747dbbd92521e747 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.health.health-alert-card','data' => ['alert' => [
                                'type' => 'queue',
                                'severity' => \App\Enums\SystemHealthStatus::Warning,
                                'title' => __('Workers Not Detected'),
                                'message' => __('Queue workers are not reporting as active. Jobs may remain pending until workers are started.'),
                            ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.health.health-alert-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['alert' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                                'type' => 'queue',
                                'severity' => \App\Enums\SystemHealthStatus::Warning,
                                'title' => __('Workers Not Detected'),
                                'message' => __('Queue workers are not reporting as active. Jobs may remain pending until workers are started.'),
                            ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf25f221051308276747dbbd92521e747)): ?>
<?php $attributes = $__attributesOriginalf25f221051308276747dbbd92521e747; ?>
<?php unset($__attributesOriginalf25f221051308276747dbbd92521e747); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf25f221051308276747dbbd92521e747)): ?>
<?php $component = $__componentOriginalf25f221051308276747dbbd92521e747; ?>
<?php unset($__componentOriginalf25f221051308276747dbbd92521e747); ?>
<?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <?php if (isset($component)) { $__componentOriginal72995217e797c5272673fc06f3c141db = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72995217e797c5272673fc06f3c141db = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.health.health-metric-card','data' => ['label' => __('Pending Jobs'),'value' => number_format($queue['pending_jobs']),'status' => $queue['pending_jobs'] > 100 ? 'warning' : 'healthy','icon' => 'clock']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.health.health-metric-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Pending Jobs')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($queue['pending_jobs'])),'status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($queue['pending_jobs'] > 100 ? 'warning' : 'healthy'),'icon' => 'clock']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72995217e797c5272673fc06f3c141db)): ?>
<?php $attributes = $__attributesOriginal72995217e797c5272673fc06f3c141db; ?>
<?php unset($__attributesOriginal72995217e797c5272673fc06f3c141db); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72995217e797c5272673fc06f3c141db)): ?>
<?php $component = $__componentOriginal72995217e797c5272673fc06f3c141db; ?>
<?php unset($__componentOriginal72995217e797c5272673fc06f3c141db); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal72995217e797c5272673fc06f3c141db = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72995217e797c5272673fc06f3c141db = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.health.health-metric-card','data' => ['label' => __('Failed Jobs'),'value' => number_format($queue['failed_jobs']),'status' => $queue['failed_jobs'] > 0 ? 'critical' : 'healthy','icon' => 'x-circle']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.health.health-metric-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Failed Jobs')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($queue['failed_jobs'])),'status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($queue['failed_jobs'] > 0 ? 'critical' : 'healthy'),'icon' => 'x-circle']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72995217e797c5272673fc06f3c141db)): ?>
<?php $attributes = $__attributesOriginal72995217e797c5272673fc06f3c141db; ?>
<?php unset($__attributesOriginal72995217e797c5272673fc06f3c141db); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72995217e797c5272673fc06f3c141db)): ?>
<?php $component = $__componentOriginal72995217e797c5272673fc06f3c141db; ?>
<?php unset($__componentOriginal72995217e797c5272673fc06f3c141db); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal72995217e797c5272673fc06f3c141db = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72995217e797c5272673fc06f3c141db = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.health.health-metric-card','data' => ['label' => __('Longest Waiting'),'value' => $queue['longest_waiting_job'],'icon' => 'switch-horizontal','class' => 'col-span-2 sm:col-span-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.health.health-metric-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Longest Waiting')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($queue['longest_waiting_job']),'icon' => 'switch-horizontal','class' => 'col-span-2 sm:col-span-1']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72995217e797c5272673fc06f3c141db)): ?>
<?php $attributes = $__attributesOriginal72995217e797c5272673fc06f3c141db; ?>
<?php unset($__attributesOriginal72995217e797c5272673fc06f3c141db); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72995217e797c5272673fc06f3c141db)): ?>
<?php $component = $__componentOriginal72995217e797c5272673fc06f3c141db; ?>
<?php unset($__componentOriginal72995217e797c5272673fc06f3c141db); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal72995217e797c5272673fc06f3c141db = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72995217e797c5272673fc06f3c141db = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.health.health-metric-card','data' => ['label' => __('Workers Running'),'value' => $queue['workers_label'],'status' => $workersMissing ? 'warning' : ($queue['workers_running'] > 0 ? 'healthy' : 'unknown'),'icon' => 'chip','class' => 'col-span-2 sm:col-span-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.health.health-metric-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Workers Running')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($queue['workers_label']),'status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($workersMissing ? 'warning' : ($queue['workers_running'] > 0 ? 'healthy' : 'unknown')),'icon' => 'chip','class' => 'col-span-2 sm:col-span-1']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72995217e797c5272673fc06f3c141db)): ?>
<?php $attributes = $__attributesOriginal72995217e797c5272673fc06f3c141db; ?>
<?php unset($__attributesOriginal72995217e797c5272673fc06f3c141db); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72995217e797c5272673fc06f3c141db)): ?>
<?php $component = $__componentOriginal72995217e797c5272673fc06f3c141db; ?>
<?php unset($__componentOriginal72995217e797c5272673fc06f3c141db); ?>
<?php endif; ?>
                    </div>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
            </section>

            
            <section class="xl:col-span-3">
                <?php if (isset($component)) { $__componentOriginal2cafaefb94b4229457cbd9e8c8c6f27a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2cafaefb94b4229457cbd9e8c8c6f27a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.health.health-progress-card','data' => ['label' => __('Storage Monitoring'),'usedLabel' => $storage['used_label'],'freeLabel' => $storage['free_label'],'percent' => $storage['usage_percent'],'status' => $storageVariant,'uploadsLabel' => $storage['uploads_label'],'backupLabel' => $storage['backup_label']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.health.health-progress-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Storage Monitoring')),'used-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($storage['used_label']),'free-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($storage['free_label']),'percent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($storage['usage_percent']),'status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($storageVariant),'uploads-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($storage['uploads_label']),'backup-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($storage['backup_label'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2cafaefb94b4229457cbd9e8c8c6f27a)): ?>
<?php $attributes = $__attributesOriginal2cafaefb94b4229457cbd9e8c8c6f27a; ?>
<?php unset($__attributesOriginal2cafaefb94b4229457cbd9e8c8c6f27a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2cafaefb94b4229457cbd9e8c8c6f27a)): ?>
<?php $component = $__componentOriginal2cafaefb94b4229457cbd9e8c8c6f27a; ?>
<?php unset($__componentOriginal2cafaefb94b4229457cbd9e8c8c6f27a); ?>
<?php endif; ?>
            </section>
        </div>

        
        <?php
            $secondaryKpis = collect($kpis)->except(['application', 'database', 'queue', 'storage', 'backup']);
        ?>
        <?php if($secondaryKpis->isNotEmpty()): ?>
            <div>
                <?php if (isset($component)) { $__componentOriginal69aa9ebf9e46f2dd640de69819b8ffdc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69aa9ebf9e46f2dd640de69819b8ffdc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.health.health-section-header','data' => ['title' => __('Platform Indicators'),'subtitle' => __('Extended service and channel monitoring')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.health.health-section-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Platform Indicators')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Extended service and channel monitoring'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69aa9ebf9e46f2dd640de69819b8ffdc)): ?>
<?php $attributes = $__attributesOriginal69aa9ebf9e46f2dd640de69819b8ffdc; ?>
<?php unset($__attributesOriginal69aa9ebf9e46f2dd640de69819b8ffdc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69aa9ebf9e46f2dd640de69819b8ffdc)): ?>
<?php $component = $__componentOriginal69aa9ebf9e46f2dd640de69819b8ffdc; ?>
<?php unset($__componentOriginal69aa9ebf9e46f2dd640de69819b8ffdc); ?>
<?php endif; ?>
                <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                    <?php $__currentLoopData = $secondaryKpis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="space-y-2">
                            <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => $kpi['label'],'value' => $kpi['value'],'hint' => $kpi['detail'],'icon' => match ($key) {
                                    'memory' => 'chip',
                                    'cpu' => 'chip',
                                    'cache' => 'cog',
                                    'session' => 'users',
                                    'mail' => 'inbox',
                                    'sms' => 'device-mobile',
                                    default => 'badge-check',
                                }]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpi['label']),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpi['value']),'hint' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpi['detail']),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(match ($key) {
                                    'memory' => 'chip',
                                    'cpu' => 'chip',
                                    'cache' => 'cog',
                                    'session' => 'users',
                                    'mail' => 'inbox',
                                    'sms' => 'device-mobile',
                                    default => 'badge-check',
                                })]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $attributes = $__attributesOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $component = $__componentOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__componentOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginal16682510d2d606e0990dc24bb6455e92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal16682510d2d606e0990dc24bb6455e92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.health.health-status-badge','data' => ['status' => $kpi['status']->value]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.health.health-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpi['status']->value)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal16682510d2d606e0990dc24bb6455e92)): ?>
<?php $attributes = $__attributesOriginal16682510d2d606e0990dc24bb6455e92; ?>
<?php unset($__attributesOriginal16682510d2d606e0990dc24bb6455e92); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal16682510d2d606e0990dc24bb6455e92)): ?>
<?php $component = $__componentOriginal16682510d2d606e0990dc24bb6455e92; ?>
<?php unset($__componentOriginal16682510d2d606e0990dc24bb6455e92); ?>
<?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        
        <section>
            <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                <?php if (isset($component)) { $__componentOriginal69aa9ebf9e46f2dd640de69819b8ffdc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69aa9ebf9e46f2dd640de69819b8ffdc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.health.health-section-header','data' => ['title' => __('Alert Center'),'subtitle' => __('Active operational alerts ranked by severity'),'status' => count($alerts) > 0 ? 'warning' : 'healthy']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.health.health-section-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Alert Center')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Active operational alerts ranked by severity')),'status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(count($alerts) > 0 ? 'warning' : 'healthy')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69aa9ebf9e46f2dd640de69819b8ffdc)): ?>
<?php $attributes = $__attributesOriginal69aa9ebf9e46f2dd640de69819b8ffdc; ?>
<?php unset($__attributesOriginal69aa9ebf9e46f2dd640de69819b8ffdc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69aa9ebf9e46f2dd640de69819b8ffdc)): ?>
<?php $component = $__componentOriginal69aa9ebf9e46f2dd640de69819b8ffdc; ?>
<?php unset($__componentOriginal69aa9ebf9e46f2dd640de69819b8ffdc); ?>
<?php endif; ?>

                <?php if($alerts === []): ?>
                    <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'badge-check','title' => __('No active alerts'),'description' => __('System is operating normally. All monitored systems are within normal operating thresholds.'),'class' => 'py-10']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'badge-check','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No active alerts')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('System is operating normally. All monitored systems are within normal operating thresholds.')),'class' => 'py-10']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
                <?php else: ?>
                    <div class="mt-4 space-y-3">
                        <?php $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if (isset($component)) { $__componentOriginalf25f221051308276747dbbd92521e747 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf25f221051308276747dbbd92521e747 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.health.health-alert-card','data' => ['alert' => $alert]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.health.health-alert-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['alert' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($alert)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf25f221051308276747dbbd92521e747)): ?>
<?php $attributes = $__attributesOriginalf25f221051308276747dbbd92521e747; ?>
<?php unset($__attributesOriginalf25f221051308276747dbbd92521e747); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf25f221051308276747dbbd92521e747)): ?>
<?php $component = $__componentOriginalf25f221051308276747dbbd92521e747; ?>
<?php unset($__componentOriginalf25f221051308276747dbbd92521e747); ?>
<?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
        </section>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $attributes = $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $component = $__componentOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\operations\health\index.blade.php ENDPATH**/ ?>
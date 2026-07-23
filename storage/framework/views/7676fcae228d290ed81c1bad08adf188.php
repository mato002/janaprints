<?php
    $byUser = collect($report['by_user']);
    $totals = $report['totals'];

    $capacityBase = max(10, (int) $byUser->max('assigned_load'));

    $teamMembers = $byUser->map(function (array $row) use ($capacityBase) {
        $capacityPercent = min(100, (int) round(($row['assigned_load'] / $capacityBase) * 100));
        $escalatedEstimate = $row['assigned_load'] > 0
            ? (int) round($row['assigned_load'] * ($row['escalation_rate'] / 100))
            : 0;

        return array_merge($row, [
            'capacity_percent' => $capacityPercent,
            'escalated_count' => $escalatedEstimate,
            'status' => match (true) {
                $capacityPercent >= 80 => 'overloaded',
                $row['assigned_load'] === 0 => 'idle',
                default => 'active',
            },
        ]);
    })->sortByDesc('assigned_load')->values();

    $rankings = $byUser->sortByDesc('conversations_handled')->values();

    $responseValues = $byUser->pluck('avg_response_minutes')->filter(fn ($v) => $v !== null);
    $resolutionValues = $byUser->pluck('avg_resolution_minutes')->filter(fn ($v) => $v !== null);

    $teamAvgFirstResponse = $responseValues->isNotEmpty()
        ? round($responseValues->avg(), 1).'m'
        : '—';

    $teamAvgResolution = $resolutionValues->isNotEmpty()
        ? round($resolutionValues->avg(), 1).'m'
        : '—';

    $teamUtilization = $teamMembers->isNotEmpty()
        ? (int) round($teamMembers->avg('capacity_percent'))
        : 0;

    $mostActive = $rankings->first();
    $fastestResponder = $byUser
        ->filter(fn ($r) => $r['avg_response_minutes'] !== null)
        ->sortBy('avg_response_minutes')
        ->first();
    $highestResolution = $byUser
        ->filter(fn ($r) => $r['avg_resolution_minutes'] !== null)
        ->sortBy('avg_resolution_minutes')
        ->first();

    if (! $fastestResponder && $byUser->isNotEmpty()) {
        $fastestResponder = $byUser->sortBy('escalation_rate')->first();
    }

    if (! $highestResolution && $byUser->isNotEmpty()) {
        $highestResolution = $byUser->sortByDesc(fn ($r) => $r['conversations_handled'] > 0 ? (100 - $r['escalation_rate']) : -1)->first();
    }

    $mostEscalations = $byUser->sortByDesc('escalation_rate')->first();
    $hasEscalationSignal = ($mostEscalations['escalation_rate'] ?? 0) > 0;

    $inboxUnassignedUrl = route('admin.communications.inbox.index', ['view' => 'unassigned']);
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Inbox team performance'),'breadcrumbs' => [['label' => __('Inbox'), 'url' => route('admin.communications.inbox.index')], ['label' => __('Team')]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="exec-team-cc">
        <header class="exec-dashboard__header">
            <div>
                <div class="comms-action-bar mb-2">
                    <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['variant' => 'primary','size' => 'sm','href' => route('admin.communications.inbox.index'),'dataTurboFrame' => 'erp-main']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','size' => 'sm','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.communications.inbox.index')),'data-turbo-frame' => 'erp-main']); ?><?php echo e(__('Open shared inbox')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $attributes = $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $component = $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('executive', App\Models\Communications\Inbox\CommunicationConversation::class)): ?>
                        <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['variant' => 'outline','size' => 'sm','href' => route('admin.communications.inbox.executive'),'dataTurboFrame' => 'erp-main']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'outline','size' => 'sm','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.communications.inbox.executive')),'data-turbo-frame' => 'erp-main']); ?><?php echo e(__('CEO view')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $attributes = $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $component = $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
                    <?php endif; ?>
                </div>
                <h1 class="exec-dashboard__title"><?php echo e(__('Team Operations Command Center')); ?></h1>
                <p class="exec-dashboard__context"><?php echo e(__('Workload, capacity, and performance at a glance — built for inbox managers.')); ?></p>
            </div>
            <span class="exec-live-badge">
                <span class="exec-live-badge__dot" aria-hidden="true"></span>
                <?php echo e(__('Live team ops')); ?>

            </span>
        </header>

        <?php echo $__env->make('admin.communications.inbox.team.partials.summary', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="exec-team-cc__main grid grid-cols-1 gap-3 xl:grid-cols-12">
            <div class="exec-team-cc__primary xl:col-span-8">
                <?php echo $__env->make('admin.communications.inbox.team.partials.workload-board', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <aside class="exec-team-cc__rail space-y-3 xl:col-span-4">
                <?php echo $__env->make('admin.communications.inbox.team.partials.rankings', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->make('admin.communications.inbox.team.partials.insights', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </aside>
        </div>

        <div class="exec-team-cc__bottom grid grid-cols-1 gap-3 lg:grid-cols-2">
            <?php echo $__env->make('admin.communications.inbox.team.partials.unassigned', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('admin.communications.inbox.team.partials.capacity', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\team.blade.php ENDPATH**/ ?>
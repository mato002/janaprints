<?php
    use App\Enums\InboxSlaStatus;

    $inboxRoute = fn ($conversationId) => route('admin.communications.inbox.index', ['conversation' => $conversationId]);

    $conversationPools = collect([
        $stats['longest_waiting'],
        $stats['recent_escalated'],
        $stats['recent_unassigned'],
        $stats['vip_waiting'],
        $stats['high_value_waiting'],
        $stats['recent_complaints'],
    ])->flatten(1)->unique('id');

    $channelKeys = [
        'whatsapp' => ['label' => __('WhatsApp')],
        'sms' => ['label' => __('SMS')],
        'email' => ['label' => __('Email')],
    ];
    $channelTotal = $conversationPools->filter(fn ($c) => filled($c->last_channel))->count();
    $channelMix = collect($channelKeys)->map(function ($meta, $key) use ($conversationPools, $channelTotal) {
        $count = $conversationPools->filter(fn ($c) => (string) $c->last_channel === $key)->count();
        $pct = $channelTotal > 0 ? (int) round(($count / $channelTotal) * 100) : 0;

        return array_merge($meta, ['key' => $key, 'count' => $count, 'percent' => $pct]);
    });

    $priorityThreads = collect()
        ->merge($stats['vip_waiting'])
        ->merge($stats['high_value_waiting'])
        ->merge($stats['recent_complaints'])
        ->unique('id')
        ->sortByDesc(fn ($c) => $c->last_activity_at?->timestamp ?? 0)
        ->take(8);

    $overdueThreads = $stats['longest_waiting']
        ->filter(fn ($c) => $c->sla_status === InboxSlaStatus::Red)
        ->take(8);

    $assigneeLoads = $conversationPools
        ->filter(fn ($c) => $c->assignee)
        ->groupBy('assigned_user_id')
        ->map(fn ($group) => [
            'name' => $group->first()->assignee->name,
            'count' => $group->count(),
        ])
        ->sortByDesc('count')
        ->values()
        ->take(6);

    $activityFeed = collect();

    foreach ($stats['recent_escalated'] as $conv) {
        $activityFeed->push([
            'at' => $conv->escalated_at ?? $conv->last_activity_at,
            'type' => 'escalation',
            'tone' => 'danger',
            'title' => __('Thread escalated'),
            'body' => $conv->display_name ?? $conv->conversation_code,
            'meta' => $conv->assignee?->name,
            'href' => $inboxRoute($conv->id),
        ]);
    }

    foreach ($stats['recent_unassigned'] as $conv) {
        $activityFeed->push([
            'at' => $conv->last_activity_at,
            'type' => 'assignment',
            'tone' => 'warning',
            'title' => __('Awaiting assignment'),
            'body' => $conv->display_name ?? $conv->conversation_code,
            'meta' => __('Unassigned'),
            'href' => $inboxRoute($conv->id),
        ]);
    }

    foreach ($stats['longest_waiting'] as $conv) {
        $activityFeed->push([
            'at' => $conv->waiting_since ?? $conv->last_activity_at,
            'type' => 'waiting',
            'tone' => 'warning',
            'title' => __('Customer waiting'),
            'body' => $conv->display_name ?? $conv->conversation_code,
            'meta' => $conv->waiting_since?->diffForHumans(),
            'href' => $inboxRoute($conv->id),
        ]);
    }

    foreach ($stats['recent_complaints'] as $conv) {
        $activityFeed->push([
            'at' => $conv->escalated_at ?? $conv->last_activity_at,
            'type' => 'complaint',
            'tone' => 'danger',
            'title' => __('Complaint / escalation signal'),
            'body' => $conv->display_name ?? $conv->conversation_code,
            'meta' => $conv->assignee?->name ?? __('Unassigned'),
            'href' => $inboxRoute($conv->id),
        ]);
    }

    $activityFeed = $activityFeed
        ->filter(fn ($item) => $item['at'] !== null)
        ->sortByDesc(fn ($item) => $item['at']->timestamp)
        ->take(14)
        ->values();
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('CEO Inbox'),'breadcrumbs' => [['label' => __('Inbox'), 'url' => route('admin.communications.inbox.index')], ['label' => __('CEO view')]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="exec-inbox-cc">
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
<?php $component->withAttributes(['variant' => 'primary','size' => 'sm','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.communications.inbox.index')),'data-turbo-frame' => 'erp-main']); ?><?php echo e(__('Open inbox')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $attributes = $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $component = $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
                </div>
                <h1 class="exec-dashboard__title"><?php echo e(__('Executive Communication Command Center')); ?></h1>
                <p class="exec-dashboard__context"><?php echo e(__('Real-time intelligence across customer threads, SLA posture, and team capacity.')); ?></p>
            </div>
            <span class="exec-live-badge">
                <span class="exec-live-badge__dot" aria-hidden="true"></span>
                <?php echo e(__('Live inbox')); ?>

            </span>
        </header>

        <div class="exec-inbox-cc__metrics-row">
            <?php echo $__env->make('admin.communications.inbox.executive.partials.health-panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('admin.communications.inbox.executive.partials.performance-panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

        <div class="exec-inbox-cc__main grid grid-cols-1 gap-3 xl:grid-cols-12">
            <div class="exec-inbox-cc__primary space-y-3 xl:col-span-8">
                <?php echo $__env->make('admin.communications.inbox.executive.partials.attention-center', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <div class="exec-inbox-cc__bottom grid grid-cols-1 gap-3 lg:grid-cols-2">
                    <?php echo $__env->make('admin.communications.inbox.executive.partials.channel-distribution', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php echo $__env->make('admin.communications.inbox.executive.partials.team-workload', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>
            <aside class="exec-inbox-cc__rail xl:col-span-4">
                <?php echo $__env->make('admin.communications.inbox.executive.partials.activity-feed', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </aside>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\executive.blade.php ENDPATH**/ ?>
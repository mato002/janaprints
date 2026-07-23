<?php
    use App\Enums\ProductionJobCardStatus;

    $workflowPresentation = $workflowPresentation ?? null;
    $controlAlerts = $controlAlerts ?? [];
    $completion = $completion ?? ['eligible' => false, 'blockers' => [], 'already_posted' => false];
    $hasPostedOutput = (bool) ($hasPostedOutput ?? ($completion['already_posted'] ?? false));

    // Completion / finished-goods / dispatch requirements belong near the end of the job —
    // not while the job is still queued or only just starting work.
    $showDownstreamRequirements = in_array($jobCard->status, [
        ProductionJobCardStatus::QualityCheck,
        ProductionJobCardStatus::Completed,
        ProductionJobCardStatus::ReadyForDispatch,
    ], true);

    $items = [];
    $seen = [];

    if ($showDownstreamRequirements) {
        foreach ($workflowPresentation['readiness_items'] ?? [] as $item) {
            if ($item['passed'] ?? true) {
                continue;
            }

            $label = (string) ($item['label'] ?? '');
            if ($label === '' || isset($seen[$label])) {
                continue;
            }

            $seen[$label] = true;
            $items[] = [
                'severity' => 'error',
                'message' => $label,
                'hint' => $item['hint'] ?? null,
                'action_url' => $item['action'] ?? null,
                'action_label' => $item['action_label'] ?? __('Resolve'),
            ];
        }
    }

    foreach ($controlAlerts as $alert) {
        $message = (string) ($alert['message'] ?? '');
        if ($message === '' || isset($seen[$message])) {
            continue;
        }

        $seen[$message] = true;
        $actionUrl = null;
        $actionLabel = null;

        if (str_contains(strtolower($message), 'artwork')) {
            $actionUrl = route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'artwork']);
            $actionLabel = __('Approve artwork');
        } elseif (str_contains(strtolower($message), 'qc')) {
            $actionUrl = route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'quality']);
            $actionLabel = __('Open QC');
        } elseif (str_contains(strtolower($message), 'material')) {
            $actionUrl = route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'material-consumption', 'open' => 'record-consumption-modal']);
            $actionLabel = __('Record consumption');
        } elseif (str_contains(strtolower($message), 'finished goods') || str_contains(strtolower($message), 'post')) {
            $actionUrl = route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'outputs']);
            $actionLabel = __('Post finished goods');
        } elseif (str_contains(strtolower($message), 'operation')) {
            $actionUrl = route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'operations']);
            $actionLabel = __('Complete operations');
        } else {
            $actionUrl = route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'dispatch']);
            $actionLabel = __('Review dispatch');
        }

        $items[] = [
            'severity' => ($alert['type'] ?? '') === 'warning' ? 'warning' : 'error',
            'message' => $message,
            'hint' => null,
            'action_url' => $actionUrl,
            'action_label' => $actionLabel,
        ];
    }

    if ($showDownstreamRequirements) {
        foreach ($completion['blockers'] ?? [] as $message) {
            $message = (string) $message;
            if ($message === '' || isset($seen[$message])) {
                continue;
            }

            if (! empty($completion['already_posted']) || ! empty($hasPostedOutput)) {
                continue;
            }

            $seen[$message] = true;
            $items[] = [
                'severity' => 'warning',
                'message' => $message,
                'hint' => null,
                'action_url' => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'outputs']),
                'action_label' => __('Open finished goods'),
            ];
        }
    }
?>

<?php if(! empty($items)): ?>
    <section class="job-360-blockers mb-4" aria-label="<?php echo e(__('Blockers')); ?>">
        <div class="job-360-blockers__head">
            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'exclamation','class' => 'h-5 w-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'exclamation','class' => 'h-5 w-5']); ?>
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
            <div>
                <h2 class="job-360-blockers__title"><?php echo e(__('Blockers')); ?></h2>
                <p class="job-360-blockers__subtitle"><?php echo e(trans_choice(':count item needs attention before the job can proceed.|:count items need attention before the job can proceed.', count($items), ['count' => count($items)])); ?></p>
            </div>
        </div>

        <ul class="job-360-blockers__list">
            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="<?php echo \Illuminate\Support\Arr::toCssClasses(['job-360-blockers__item', 'job-360-blockers__item--'.$item['severity']]); ?>">
                    <div class="job-360-blockers__content">
                        <p class="job-360-blockers__message"><?php echo e($item['message']); ?></p>
                        <?php if($item['hint'] ?? null): ?>
                            <p class="job-360-blockers__hint"><?php echo e($item['hint']); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if($item['action_url'] ?? null): ?>
                        <a href="<?php echo e($item['action_url']); ?>" class="job-360-blockers__action" data-turbo-frame="erp-main">
                            <?php echo e($item['action_label']); ?> →
                        </a>
                    <?php endif; ?>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </section>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\partials\blockers-panel.blade.php ENDPATH**/ ?>
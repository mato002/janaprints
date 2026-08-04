<?php
    use App\Enums\ProductionJobCardStatus;
    use App\Support\Navigation\WorkspaceEmbed;

    $workflowPresentation = $workflowPresentation ?? null;
    $controlAlerts = $controlAlerts ?? [];
    $completion = $completion ?? ['eligible' => false, 'blockers' => [], 'already_posted' => false];
    $hasPostedOutput = (bool) ($hasPostedOutput ?? ($completion['already_posted'] ?? false));
    $materialReadiness = is_array($materialReadiness ?? null) ? $materialReadiness : null;
    $executionState = $executionState ?? [];

    $showDownstreamRequirements = in_array($jobCard->status, [
        ProductionJobCardStatus::QualityCheck,
        ProductionJobCardStatus::Completed,
        ProductionJobCardStatus::ReadyForDispatch,
    ], true);

    $showMaterialReleaseGate = in_array($jobCard->status, [
        ProductionJobCardStatus::Draft,
        ProductionJobCardStatus::Queued,
        ProductionJobCardStatus::Rework,
        ProductionJobCardStatus::OnHold,
    ], true);

    $items = [];
    $seen = [];
    $resolveUrl = null;

    if ($showMaterialReleaseGate && $materialReadiness && ! ($materialReadiness['ready'] ?? false)) {
        $message = ! ($materialReadiness['has_requirements'] ?? false)
            ? __('Material requirements missing')
            : __('Material shortages block release');

        $seen[$message] = true;
        $resolveUrl ??= $materialReadiness['materials_url']
            ?? route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'materials']);
        $items[] = [
            'severity' => 'error',
            'message' => $message,
        ];
    }

    if ($showMaterialReleaseGate && ($executionState['needs_operator'] ?? false)) {
        $message = __('Operator not assigned');
        if (! isset($seen[$message])) {
            $seen[$message] = true;
            $items[] = ['severity' => 'error', 'message' => $message];
            $resolveUrl ??= route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'overview']).'#assign-operator';
        }
    }

    if ($showMaterialReleaseGate && ($executionState['needs_machine'] ?? false)) {
        $message = __('Machine not assigned');
        if (! isset($seen[$message])) {
            $seen[$message] = true;
            $items[] = ['severity' => 'error', 'message' => $message];
            $resolveUrl ??= route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'overview']).'#assign-machine';
        }
    }

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
            $resolveUrl ??= $item['action'] ?? null;
            $items[] = [
                'severity' => 'error',
                'message' => $label,
            ];
        }
    }

    foreach ($controlAlerts as $alert) {
        $message = (string) ($alert['message'] ?? '');
        if ($message === '' || isset($seen[$message])) {
            continue;
        }

        $seen[$message] = true;
        $lower = strtolower($message);

        if ($resolveUrl === null) {
            $resolveUrl = match (true) {
                str_contains($lower, 'artwork') => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'artwork']),
                str_contains($lower, 'qc') => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'quality']),
                str_contains($lower, 'shortag'), str_contains($lower, 'readiness'), str_contains($lower, 'requirements'), str_contains($lower, 'material') => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'materials']),
                str_contains($lower, 'finished goods'), str_contains($lower, 'post') => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'outputs']),
                str_contains($lower, 'operation') => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'operations']),
                default => route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'dispatch']),
            };
        }

        $items[] = [
            'severity' => ($alert['type'] ?? '') === 'warning' ? 'warning' : 'error',
            'message' => $message,
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
            $resolveUrl ??= route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'outputs']);
            $items[] = [
                'severity' => 'warning',
                'message' => $message,
            ];
        }
    }
    $compact = (bool) ($compact ?? false);
?>

<?php if($compact): ?>
    <?php if(! empty($items)): ?>
        <div class="job-360-blockers mes-blockers">
            <div class="job-360-blockers__head">
                <span class="job-360-blockers__title">🚨 <?php echo e(__('Blockers')); ?> (<?php echo e(count($items)); ?>)</span>
                <?php if($resolveUrl): ?>
                    <a href="<?php echo e($resolveUrl); ?>" class="job-360-blockers__resolve" <?php $__currentLoopData = WorkspaceEmbed::leaveWorkspaceLinkAttributes(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>>
                        <?php echo e(__('Resolve')); ?> →
                    </a>
                <?php endif; ?>
            </div>
            <ul class="job-360-blockers__list">
                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="<?php echo \Illuminate\Support\Arr::toCssClasses(['job-360-blockers__item', 'job-360-blockers__item--warning' => ($item['severity'] ?? '') === 'warning']); ?>">
                        <?php echo e($item['message']); ?>

                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php else: ?>
        <div class="job-360-blockers mes-blockers mes-blockers--clear">
            <div class="job-360-blockers__head">
                <span class="job-360-blockers__title text-emerald-800">✓ <?php echo e(__('No blockers')); ?></span>
            </div>
        </div>
    <?php endif; ?>
<?php elseif(! empty($items)): ?>
    <?php if (isset($component)) { $__componentOriginalf57220fba53d148717b4781691527db9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf57220fba53d148717b4781691527db9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.job-module-card','data' => ['theme' => 'alert','title' => __('Blockers'),'icon' => 'exclamation','compact' => true,'class' => 'h-full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.job-module-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['theme' => 'alert','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Blockers')),'icon' => 'exclamation','compact' => true,'class' => 'h-full']); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if($resolveUrl): ?>
                <a href="<?php echo e($resolveUrl); ?>" class="text-xs font-semibold text-red-700 hover:underline" <?php $__currentLoopData = WorkspaceEmbed::leaveWorkspaceLinkAttributes(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>>
                    <?php echo e(__('Resolve')); ?> →
                </a>
            <?php endif; ?>
         <?php $__env->endSlot(); ?>

        <p class="mb-2 text-xs font-medium text-red-800"><?php echo e(trans_choice(':count issue blocking release|:count issues blocking release', count($items), ['count' => count($items)])); ?></p>

        <ul class="space-y-1.5">
            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'flex items-start gap-2 rounded-md px-2 py-1.5 text-sm',
                    'bg-red-100/70 text-red-900' => ($item['severity'] ?? '') === 'error',
                    'bg-amber-100/70 text-amber-900' => ($item['severity'] ?? '') === 'warning',
                ]); ?>">
                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-current"></span>
                    <span><?php echo e($item['message']); ?></span>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf57220fba53d148717b4781691527db9)): ?>
<?php $attributes = $__attributesOriginalf57220fba53d148717b4781691527db9; ?>
<?php unset($__attributesOriginalf57220fba53d148717b4781691527db9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf57220fba53d148717b4781691527db9)): ?>
<?php $component = $__componentOriginalf57220fba53d148717b4781691527db9; ?>
<?php unset($__componentOriginalf57220fba53d148717b4781691527db9); ?>
<?php endif; ?>
<?php else: ?>
    <?php if (isset($component)) { $__componentOriginalf57220fba53d148717b4781691527db9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf57220fba53d148717b4781691527db9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.job-module-card','data' => ['theme' => 'materials','title' => __('Blockers'),'icon' => 'badge-check','compact' => true,'class' => 'h-full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.job-module-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['theme' => 'materials','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Blockers')),'icon' => 'badge-check','compact' => true,'class' => 'h-full']); ?>
        <p class="text-sm font-medium text-emerald-800"><?php echo e(__('No blockers — clear to proceed')); ?></p>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf57220fba53d148717b4781691527db9)): ?>
<?php $attributes = $__attributesOriginalf57220fba53d148717b4781691527db9; ?>
<?php unset($__attributesOriginalf57220fba53d148717b4781691527db9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf57220fba53d148717b4781691527db9)): ?>
<?php $component = $__componentOriginalf57220fba53d148717b4781691527db9; ?>
<?php unset($__componentOriginalf57220fba53d148717b4781691527db9); ?>
<?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\partials\blockers-panel.blade.php ENDPATH**/ ?>
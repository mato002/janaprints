<?php
    $rail = $workspace['sidebar'];
    $score = $rail['lead_score'] ?? $workspace['lead_score'];
    $next = $workspace['next_action'];
?>

<div class="space-y-4">
    <?php if (isset($component)) { $__componentOriginalca2e43e67f99a6681e7586ef239d6b0c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalca2e43e67f99a6681e7586ef239d6b0c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.record-workspace.rail-card','data' => ['title' => __('Opportunity')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.record-workspace.rail-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Opportunity'))]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <span class="rw-score rw-score--<?php echo e($score['variant']); ?>"><?php echo e($score['label']); ?></span>
         <?php $__env->endSlot(); ?>

        <div class="mb-4 space-y-3">
            <?php if (isset($component)) { $__componentOriginalac901dc0882ac71f6764df9a63796066 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalac901dc0882ac71f6764df9a63796066 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.record-workspace.meter','data' => ['label' => __('Opportunity score'),'value' => $score['points'],'display' => $score['points'].'%','hint' => $score['hint']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.record-workspace.meter'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Opportunity score')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($score['points']),'display' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($score['points'].'%'),'hint' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($score['hint'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalac901dc0882ac71f6764df9a63796066)): ?>
<?php $attributes = $__attributesOriginalac901dc0882ac71f6764df9a63796066; ?>
<?php unset($__attributesOriginalac901dc0882ac71f6764df9a63796066); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalac901dc0882ac71f6764df9a63796066)): ?>
<?php $component = $__componentOriginalac901dc0882ac71f6764df9a63796066; ?>
<?php unset($__componentOriginalac901dc0882ac71f6764df9a63796066); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginalac901dc0882ac71f6764df9a63796066 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalac901dc0882ac71f6764df9a63796066 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.record-workspace.meter','data' => ['label' => __('Win probability'),'value' => $rail['probability_value'],'display' => $rail['probability']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.record-workspace.meter'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Win probability')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($rail['probability_value']),'display' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($rail['probability'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalac901dc0882ac71f6764df9a63796066)): ?>
<?php $attributes = $__attributesOriginalac901dc0882ac71f6764df9a63796066; ?>
<?php unset($__attributesOriginalac901dc0882ac71f6764df9a63796066); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalac901dc0882ac71f6764df9a63796066)): ?>
<?php $component = $__componentOriginalac901dc0882ac71f6764df9a63796066; ?>
<?php unset($__componentOriginalac901dc0882ac71f6764df9a63796066); ?>
<?php endif; ?>
        </div>

        <dl class="rw-rail-list">
            <div>
                <dt><?php echo e(__('Status')); ?></dt>
                <dd><?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => $rail['status']->badgeVariant()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($rail['status']->badgeVariant())]); ?><?php echo e($rail['status']->workspaceLabel()); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $attributes = $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $component = $__componentOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?></dd>
            </div>
            <div>
                <dt><?php echo e(__('Priority')); ?></dt>
                <dd><?php echo e($rail['priority']); ?></dd>
            </div>
            <div>
                <dt><?php echo e(__('Assigned')); ?></dt>
                <dd><?php echo e($rail['assigned_to']); ?></dd>
            </div>
            <div>
                <dt><?php echo e(__('Expected value')); ?></dt>
                <dd><?php echo e($rail['expected_value']); ?></dd>
            </div>
            <div>
                <dt><?php echo e(__('Follow-up')); ?></dt>
                <dd><?php echo e($rail['follow_up_at']); ?></dd>
            </div>
            <div>
                <dt><?php echo e(__('Next action')); ?></dt>
                <dd class="font-semibold text-sky-700"><?php echo e($next['label']); ?></dd>
            </div>
            <div>
                <dt><?php echo e(__('Last activity')); ?></dt>
                <dd><?php echo e($rail['last_activity']?->diffForHumans() ?? '—'); ?></dd>
            </div>
        </dl>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalca2e43e67f99a6681e7586ef239d6b0c)): ?>
<?php $attributes = $__attributesOriginalca2e43e67f99a6681e7586ef239d6b0c; ?>
<?php unset($__attributesOriginalca2e43e67f99a6681e7586ef239d6b0c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalca2e43e67f99a6681e7586ef239d6b0c)): ?>
<?php $component = $__componentOriginalca2e43e67f99a6681e7586ef239d6b0c; ?>
<?php unset($__componentOriginalca2e43e67f99a6681e7586ef239d6b0c); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginalca2e43e67f99a6681e7586ef239d6b0c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalca2e43e67f99a6681e7586ef239d6b0c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.record-workspace.rail-card','data' => ['title' => __('Customer contact')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.record-workspace.rail-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Customer contact'))]); ?>
        <dl class="rw-rail-list">
            <div>
                <dt><?php echo e(__('Phone')); ?></dt>
                <dd><a href="tel:<?php echo e(preg_replace('/\s+/', '', $rail['phone'])); ?>" class="rw-hero-snapshot__link"><?php echo e($rail['phone']); ?></a></dd>
            </div>
            <div>
                <dt><?php echo e(__('Email')); ?></dt>
                <dd><a href="mailto:<?php echo e($rail['email']); ?>" class="rw-hero-snapshot__link truncate"><?php echo e($rail['email']); ?></a></dd>
            </div>
            <div>
                <dt><?php echo e(__('Artwork files')); ?></dt>
                <dd><?php echo e($rail['artwork_count']); ?></dd>
            </div>
            <div>
                <dt><?php echo e(__('Source')); ?></dt>
                <dd><?php echo e($rail['source']); ?></dd>
            </div>
            <?php if($rail['deadline']): ?>
                <div>
                    <dt><?php echo e(__('Deadline')); ?></dt>
                    <dd><?php echo e($rail['deadline']); ?></dd>
                </div>
            <?php endif; ?>
        </dl>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalca2e43e67f99a6681e7586ef239d6b0c)): ?>
<?php $attributes = $__attributesOriginalca2e43e67f99a6681e7586ef239d6b0c; ?>
<?php unset($__attributesOriginalca2e43e67f99a6681e7586ef239d6b0c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalca2e43e67f99a6681e7586ef239d6b0c)): ?>
<?php $component = $__componentOriginalca2e43e67f99a6681e7586ef239d6b0c; ?>
<?php unset($__componentOriginalca2e43e67f99a6681e7586ef239d6b0c); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginalca2e43e67f99a6681e7586ef239d6b0c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalca2e43e67f99a6681e7586ef239d6b0c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.record-workspace.rail-card','data' => ['title' => __('Conversion progress')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.record-workspace.rail-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Conversion progress'))]); ?>
        <ol class="space-y-2" role="list">
            <?php $__currentLoopData = $workspace['conversion']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="flex items-center justify-between gap-2 text-sm">
                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'font-medium',
                        'text-emerald-700' => $step['linked'],
                        'text-slate-500' => ! $step['linked'],
                    ]); ?>">
                        <?php if($step['linked']): ?> ✓ <?php else: ?> ○ <?php endif; ?>
                        <?php echo e($step['label']); ?>

                    </span>
                    <?php if($step['linked'] && ! empty($step['url'])): ?>
                        <a href="<?php echo e($step['url']); ?>" class="text-xs font-semibold text-erp-accent hover:underline" data-turbo-frame="erp-main"><?php echo e(__('Open')); ?></a>
                    <?php elseif(! $step['linked'] && ! empty($step['url'])): ?>
                        <a href="<?php echo e($step['url']); ?>" class="text-xs font-semibold text-slate-500 hover:text-erp-accent" data-turbo-frame="erp-main"><?php echo e(__('Start')); ?></a>
                    <?php endif; ?>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ol>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalca2e43e67f99a6681e7586ef239d6b0c)): ?>
<?php $attributes = $__attributesOriginalca2e43e67f99a6681e7586ef239d6b0c; ?>
<?php unset($__attributesOriginalca2e43e67f99a6681e7586ef239d6b0c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalca2e43e67f99a6681e7586ef239d6b0c)): ?>
<?php $component = $__componentOriginalca2e43e67f99a6681e7586ef239d6b0c; ?>
<?php unset($__componentOriginalca2e43e67f99a6681e7586ef239d6b0c); ?>
<?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\customer-service\quote-requests\workspace\sidebar.blade.php ENDPATH**/ ?>
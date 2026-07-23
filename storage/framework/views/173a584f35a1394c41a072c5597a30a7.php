<section class="exec-panel exec-panel--attention exec-team-cc__unassigned" aria-label="<?php echo e(__('Unassigned conversations')); ?>">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title"><?php echo e(__('Unassigned Conversations')); ?></h2>
        <span class="exec-badge exec-badge--<?php echo e($totals['unassigned'] > 0 ? 'warning' : 'muted'); ?>"><?php echo e($totals['unassigned']); ?></span>
    </div>

    <?php if($totals['unassigned'] === 0): ?>
        <?php if (isset($component)) { $__componentOriginal1300bd4fc578b3dfcc7422a709312fdd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.exec-empty-state','data' => ['title' => __('Queue is clear'),'description' => __('All active conversations have an owner.'),'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.exec-empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Queue is clear')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('All active conversations have an owner.')),'compact' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd)): ?>
<?php $attributes = $__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd; ?>
<?php unset($__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1300bd4fc578b3dfcc7422a709312fdd)): ?>
<?php $component = $__componentOriginal1300bd4fc578b3dfcc7422a709312fdd; ?>
<?php unset($__componentOriginal1300bd4fc578b3dfcc7422a709312fdd); ?>
<?php endif; ?>
    <?php else: ?>
        <article class="exec-team-cc__queue-card">
            <p class="exec-team-cc__queue-count"><?php echo e($totals['unassigned']); ?></p>
            <p class="exec-team-cc__queue-title"><?php echo e(trans_choice('conversation needs assignment|conversations need assignment', $totals['unassigned'])); ?></p>
            <dl class="exec-team-cc__unassigned-meta exec-team-cc__unassigned-meta--queue">
                <div>
                    <dt><?php echo e(__('Assigned status')); ?></dt>
                    <dd class="text-amber-700"><?php echo e(__('None')); ?></dd>
                </div>
                <div>
                    <dt><?php echo e(__('Waiting')); ?></dt>
                    <dd><?php echo e(__('Open inbox for wait times')); ?></dd>
                </div>
                <div>
                    <dt><?php echo e(__('Priority')); ?></dt>
                    <dd><?php echo e(__('Open inbox for priority')); ?></dd>
                </div>
            </dl>
            <a href="<?php echo e($inboxUnassignedUrl); ?>" class="erp-btn erp-btn--primary erp-btn--sm mt-3" data-turbo-frame="erp-main">
                <?php echo e(__('Review & assign in Shared Inbox')); ?>

            </a>
        </article>
        <p class="mt-2 text-[10px] text-slate-500"><?php echo e(__('Customer names and SLA details are shown in the inbox unassigned view.')); ?></p>
    <?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\team\partials\unassigned.blade.php ENDPATH**/ ?>
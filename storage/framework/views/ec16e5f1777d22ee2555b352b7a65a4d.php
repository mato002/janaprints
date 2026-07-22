<section class="exec-panel exec-panel--activity exec-inbox-cc__activity h-full" aria-label="<?php echo e(__('Executive activity feed')); ?>">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title"><?php echo e(__('Executive Activity Feed')); ?></h2>
        <span class="exec-panel__meta"><?php echo e(__('Newest first')); ?></span>
    </div>
    <p class="mb-2 text-[10px] leading-snug text-slate-500"><?php echo e(__('Replies, assignments, escalations, and customer interactions across monitored threads.')); ?></p>

    <div class="exec-activity-feed exec-activity-feed--prominent exec-inbox-cc__feed">
        <?php $__empty_1 = true; $__currentLoopData = $activityFeed; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $dotClass = match ($event['tone']) {
                    'danger' => 'exec-inbox-cc__feed-dot--danger',
                    'warning' => 'exec-inbox-cc__feed-dot--warning',
                    default => 'exec-inbox-cc__feed-dot--default',
                };
            ?>
            <article class="exec-inbox-cc__feed-item">
                <span class="exec-inbox-cc__feed-dot <?php echo e($dotClass); ?>" aria-hidden="true"></span>
                <div class="exec-inbox-cc__feed-body">
                    <div class="exec-inbox-cc__feed-head">
                        <span class="exec-inbox-cc__feed-type"><?php echo e($event['title']); ?></span>
                        <time class="exec-inbox-cc__feed-time" datetime="<?php echo e($event['at']->toIso8601String()); ?>"><?php echo e($event['at']->diffForHumans()); ?></time>
                    </div>
                    <a href="<?php echo e($event['href']); ?>" class="exec-inbox-cc__feed-link" data-turbo-frame="erp-main"><?php echo e($event['body']); ?></a>
                    <?php if($event['meta']): ?>
                        <p class="exec-inbox-cc__feed-meta"><?php echo e($event['meta']); ?></p>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php if (isset($component)) { $__componentOriginal1300bd4fc578b3dfcc7422a709312fdd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.exec-empty-state','data' => ['title' => __('No recent inbox events'),'description' => __('Escalations, assignments, and waiting threads will appear here.'),'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.exec-empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No recent inbox events')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Escalations, assignments, and waiting threads will appear here.')),'compact' => true]); ?>
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
        <?php endif; ?>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\executive\partials\activity-feed.blade.php ENDPATH**/ ?>
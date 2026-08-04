<section class="exec-panel exec-inbox-cc__section-panel" aria-label="<?php echo e(__('Channel distribution')); ?>">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title"><?php echo e(__('Channel Distribution')); ?></h2>
        <span class="exec-panel__meta"><?php echo e(__('Monitored thread mix')); ?></span>
    </div>

    <?php if($channelTotal === 0): ?>
        <?php if (isset($component)) { $__componentOriginal1300bd4fc578b3dfcc7422a709312fdd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.exec-empty-state','data' => ['title' => __('No channel data yet'),'description' => __('Last-used channel appears once threads receive messages.'),'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.exec-empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No channel data yet')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Last-used channel appears once threads receive messages.')),'compact' => true]); ?>
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
        <div class="exec-inbox-cc__channel-list">
            <?php $__currentLoopData = $channelMix; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $channel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="exec-inbox-cc__channel-row">
                    <div class="exec-inbox-cc__channel-label">
                        <span><?php echo e($channel['label']); ?></span>
                        <span class="exec-inbox-cc__channel-pct"><?php echo e($channel['percent']); ?>%</span>
                    </div>
                    <div class="exec-progress__track" role="progressbar" aria-valuenow="<?php echo e($channel['percent']); ?>" aria-valuemin="0" aria-valuemax="100">
                        <div class="exec-progress__bar exec-inbox-cc__channel-bar exec-inbox-cc__channel-bar--<?php echo e($channel['key']); ?>" style="width: <?php echo e(max($channel['percent'], $channel['percent'] > 0 ? 4 : 0)); ?>%"></div>
                    </div>
                    <span class="exec-inbox-cc__channel-count"><?php echo e(trans_choice(':count thread|:count threads', $channel['count'], ['count' => $channel['count']])); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\executive\partials\channel-distribution.blade.php ENDPATH**/ ?>
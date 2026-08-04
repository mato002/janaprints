<section class="exec-panel exec-team-cc__rankings" aria-label="<?php echo e(__('Top performers')); ?>">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title"><?php echo e(__('Top Performers')); ?></h2>
        <span class="exec-panel__meta"><?php echo e(__('By volume handled')); ?></span>
    </div>

    <?php if($rankings->isEmpty()): ?>
        <?php if (isset($component)) { $__componentOriginal1300bd4fc578b3dfcc7422a709312fdd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.exec-empty-state','data' => ['title' => __('No rankings yet'),'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.exec-empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No rankings yet')),'compact' => true]); ?>
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
        <ol class="exec-team-cc__leaderboard" role="list">
            <?php $__currentLoopData = $rankings->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="exec-team-cc__leaderboard-item">
                    <span class="exec-team-cc__leaderboard-rank"><?php echo e($index + 1); ?></span>
                    <div class="exec-team-cc__leaderboard-body">
                        <span class="exec-team-cc__leaderboard-name"><?php echo e($row['user']); ?></span>
                        <div class="exec-team-cc__leaderboard-meta">
                            <span><?php echo e(__('Handled')); ?>: <strong><?php echo e($row['conversations_handled']); ?></strong></span>
                            <span><?php echo e(__('Avg response')); ?>: <strong><?php echo e($row['avg_response_minutes'] ?? '—'); ?></strong></span>
                            <span><?php echo e(__('Resolution')); ?>: <strong><?php echo e($row['avg_resolution_minutes'] ?? ($row['escalation_rate'] > 0 ? (100 - $row['escalation_rate']).'%' : '—')); ?></strong></span>
                        </div>
                    </div>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ol>
    <?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\team\partials\rankings.blade.php ENDPATH**/ ?>
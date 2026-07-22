<section class="exec-panel exec-team-cc__capacity-panel" aria-label="<?php echo e(__('Team capacity')); ?>">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title"><?php echo e(__('Team Capacity')); ?></h2>
        <span class="exec-panel__meta"><?php echo e(__('Relative load · base :n', ['n' => $capacityBase])); ?></span>
    </div>

    <?php if($teamMembers->isEmpty()): ?>
        <?php if (isset($component)) { $__componentOriginal1300bd4fc578b3dfcc7422a709312fdd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.exec-empty-state','data' => ['title' => __('No capacity data'),'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.exec-empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No capacity data')),'compact' => true]); ?>
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
        <ul class="exec-team-cc__capacity-list" role="list">
            <?php $__currentLoopData = $teamMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $capacityTone = match (true) {
                        $member['capacity_percent'] >= 80 => 'danger',
                        $member['capacity_percent'] <= 20 => 'success',
                        default => 'default',
                    };
                    $barVariant = match ($capacityTone) {
                        'danger' => 'exec-progress__bar--danger',
                        'success' => 'exec-progress__bar--success',
                        default => '',
                    };
                ?>
                <li class="exec-team-cc__capacity-row exec-team-cc__capacity-row--<?php echo e($capacityTone); ?>">
                    <div class="exec-team-cc__capacity-label">
                        <span class="exec-team-cc__capacity-name"><?php echo e($member['user']); ?></span>
                        <span class="exec-team-cc__capacity-pct tabular-nums"><?php echo e($member['capacity_percent']); ?>%</span>
                    </div>
                    <div class="exec-progress__track exec-team-cc__capacity-track" role="progressbar" aria-valuenow="<?php echo e($member['capacity_percent']); ?>" aria-valuemin="0" aria-valuemax="100">
                        <div class="exec-progress__bar <?php echo e($barVariant); ?>" style="width: <?php echo e(max($member['capacity_percent'], $member['assigned_load'] > 0 ? 4 : 0)); ?>%"></div>
                    </div>
                    <?php if($member['status'] === 'overloaded'): ?>
                        <span class="exec-team-cc__capacity-flag"><?php echo e(__('Overloaded — consider redistribution')); ?></span>
                    <?php elseif($member['status'] === 'idle'): ?>
                        <span class="exec-team-cc__capacity-flag exec-team-cc__capacity-flag--idle"><?php echo e(__('Underutilized — available for assignments')); ?></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    <?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\team\partials\capacity.blade.php ENDPATH**/ ?>
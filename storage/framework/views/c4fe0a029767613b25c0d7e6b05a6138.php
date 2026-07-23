<section class="exec-panel exec-team-cc__workload-panel" aria-label="<?php echo e(__('Team workload board')); ?>">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title"><?php echo e(__('Team Workload Board')); ?></h2>
        <span class="exec-panel__meta"><?php echo e(__('Capacity vs assigned load')); ?></span>
    </div>

    <?php if($teamMembers->isEmpty()): ?>
        <?php if (isset($component)) { $__componentOriginal1300bd4fc578b3dfcc7422a709312fdd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1300bd4fc578b3dfcc7422a709312fdd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.exec-empty-state','data' => ['title' => __('No team members'),'description' => __('Add users to this company to track inbox workload.'),'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.exec-empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No team members')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Add users to this company to track inbox workload.')),'compact' => true]); ?>
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
        <div class="exec-team-cc__workload-grid">
            <?php $__currentLoopData = $teamMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $statusLabel = match ($member['status']) {
                        'overloaded' => __('Overloaded'),
                        'idle' => __('Available'),
                        default => __('Active'),
                    };
                    $statusClass = match ($member['status']) {
                        'overloaded' => 'exec-team-cc__status--danger',
                        'idle' => 'exec-team-cc__status--muted',
                        default => 'exec-team-cc__status--success',
                    };
                    $barClass = match ($member['status']) {
                        'overloaded' => 'exec-progress__bar--danger',
                        'idle' => 'exec-team-cc__bar--idle',
                        default => 'exec-progress__bar',
                    };
                ?>
                <article class="exec-team-cc__member-card exec-team-cc__member-card--<?php echo e($member['status']); ?>">
                    <div class="exec-team-cc__member-head">
                        <h3 class="exec-team-cc__member-name"><?php echo e($member['user']); ?></h3>
                        <span class="exec-team-cc__status <?php echo e($statusClass); ?>"><?php echo e($statusLabel); ?></span>
                    </div>

                    <div class="exec-team-cc__member-bar-block">
                        <div class="exec-team-cc__member-bar-label">
                            <span><?php echo e(__('Conversations')); ?></span>
                            <span class="tabular-nums"><?php echo e($member['assigned_load']); ?> / <?php echo e($capacityBase); ?></span>
                        </div>
                        <div class="exec-progress__track exec-team-cc__member-track" role="progressbar" aria-valuenow="<?php echo e($member['capacity_percent']); ?>" aria-valuemin="0" aria-valuemax="100">
                            <div class="exec-progress__bar <?php echo e($barClass); ?>" style="width: <?php echo e(max($member['capacity_percent'], $member['assigned_load'] > 0 ? 6 : 0)); ?>%"></div>
                        </div>
                    </div>

                    <dl class="exec-team-cc__member-stats">
                        <div>
                            <dt><?php echo e(__('Handled')); ?></dt>
                            <dd><?php echo e($member['conversations_handled']); ?></dd>
                        </div>
                        <div>
                            <dt><?php echo e(__('Assigned load')); ?></dt>
                            <dd><?php echo e($member['assigned_load']); ?></dd>
                        </div>
                        <div>
                            <dt><?php echo e(__('Escalated')); ?></dt>
                            <dd><?php echo e($member['escalated_count']); ?></dd>
                        </div>
                        <div>
                            <dt><?php echo e(__('Capacity used')); ?></dt>
                            <dd class="<?php echo e($member['capacity_percent'] >= 80 ? 'text-red-600' : ($member['capacity_percent'] <= 20 ? 'text-emerald-600' : '')); ?>"><?php echo e($member['capacity_percent']); ?>%</dd>
                        </div>
                    </dl>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\team\partials\workload-board.blade.php ENDPATH**/ ?>
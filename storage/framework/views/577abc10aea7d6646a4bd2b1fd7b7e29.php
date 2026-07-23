<?php
    $categoryTone = [
        'lifecycle' => 'indigo',
        'compensation' => 'violet',
        'payroll' => 'violet',
        'leave' => 'sky',
        'attendance' => 'info',
        'training' => 'teal',
        'performance' => 'amber',
        'documents' => 'slate',
        'warning' => 'warning',
        'exit' => 'danger',
        'profile' => 'slate',
    ];
?>

<section class="employee-360__timeline-card">
    <div class="employee-360__card-head">
        <div class="employee-360__card-title-wrap">
            <span class="employee-360__card-icon employee-360__card-icon--timeline" aria-hidden="true">
                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'clock','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'clock','class' => 'h-4 w-4']); ?>
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
            </span>
            <h2 class="employee-360__card-title"><?php echo e(__('Employee Timeline')); ?></h2>
        </div>
        <span class="employee-360__timeline-count"><?php echo e($timeline->count()); ?> <?php echo e(__('events')); ?></span>
    </div>

    <?php if($timeline->isEmpty()): ?>
        <div class="employee-360__empty-block employee-360__empty-block--lg">
            <p><?php echo e(__('No timeline events yet.')); ?></p>
            <p class="employee-360__empty-hint"><?php echo e(__('Clock-ins, leave, payroll, training, and profile changes will appear here.')); ?></p>
        </div>
    <?php else: ?>
        <ol class="employee-360__timeline">
            <?php $__currentLoopData = $timeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $tone = $categoryTone[strtolower($event->category)] ?? 'slate';
                ?>
                <li class="employee-360__timeline-item employee-360__timeline-item--<?php echo e($tone); ?>">
                    <span class="employee-360__timeline-dot" aria-hidden="true"></span>
                    <div class="employee-360__timeline-body">
                        <div class="employee-360__timeline-top">
                            <time class="employee-360__timeline-time" datetime="<?php echo e($event->eventDatetime->toIso8601String()); ?>">
                                <?php echo e($event->eventDatetime->format('d M Y · H:i')); ?>

                            </time>
                            <span class="employee-360__timeline-cat"><?php echo e($event->category); ?></span>
                        </div>
                        <p class="employee-360__timeline-title"><?php echo e($event->title); ?></p>
                        <?php if($event->description): ?>
                            <p class="employee-360__timeline-desc"><?php echo e($event->description); ?></p>
                        <?php endif; ?>
                        <?php if($event->actorName): ?>
                            <p class="employee-360__timeline-actor"><?php echo e(__('By')); ?> <?php echo e($event->actorName); ?></p>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ol>
    <?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\employees\360\tabs\timeline.blade.php ENDPATH**/ ?>
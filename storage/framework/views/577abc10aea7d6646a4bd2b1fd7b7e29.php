<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <h3 class="mb-4 font-semibold text-erp-primary"><?php echo e(__('Employee Timeline')); ?></h3>
    <ol class="relative border-s border-slate-200 ms-3 space-y-6">
        <?php $__empty_1 = true; $__currentLoopData = $timeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <li class="ms-6">
                <span class="absolute -start-1.5 mt-1.5 h-3 w-3 rounded-full bg-erp-primary"></span>
                <div class="text-sm">
                    <time class="text-xs text-slate-500"><?php echo e($event->eventDatetime->format('M j, Y H:i')); ?></time>
                    <p class="font-medium text-erp-primary"><?php echo e($event->title); ?></p>
                    <?php if($event->description): ?>
                        <p class="text-slate-600"><?php echo e($event->description); ?></p>
                    <?php endif; ?>
                    <?php if($event->actorName): ?>
                        <p class="text-xs text-slate-500"><?php echo e(__('By')); ?> <?php echo e($event->actorName); ?></p>
                    <?php endif; ?>
                    <span class="mt-1 inline-block rounded bg-slate-100 px-2 py-0.5 text-[10px] uppercase tracking-wide text-slate-600"><?php echo e($event->category); ?></span>
                </div>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <li class="ms-6 text-sm text-slate-500"><?php echo e(__('No timeline events yet.')); ?></li>
        <?php endif; ?>
    </ol>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\employees\360\tabs\timeline.blade.php ENDPATH**/ ?>
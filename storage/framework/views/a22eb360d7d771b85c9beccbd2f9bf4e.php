<?php if(count($receivingPipeline ?? []) > 0 || count($issuePipeline ?? []) > 0): ?>
    <div class="mb-4 grid gap-4 lg:grid-cols-2">
        <?php if(count($receivingPipeline ?? []) > 0): ?>
            <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['padding' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
                <div class="border-b border-erp-border px-4 py-3">
                    <h2 class="text-sm font-semibold text-slate-900"><?php echo e(__('Awaiting delivery')); ?></h2>
                </div>
                <ul class="divide-y divide-slate-100">
                    <?php $__currentLoopData = $receivingPipeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>
                            <a
                                href="<?php echo e($row['url']); ?>"
                                class="flex items-center justify-between gap-3 px-4 py-3 text-sm transition hover:bg-slate-50"
                                <?php if($row['modal'] ?? false): ?> data-erp-modal-open <?php else: ?> data-turbo-frame="_top" <?php endif; ?>
                            >
                                <span class="min-w-0">
                                    <span class="block font-mono text-xs font-medium text-slate-900"><?php echo e($row['label']); ?></span>
                                    <span class="block truncate text-xs text-slate-500"><?php echo e($row['supplier']); ?></span>
                                </span>
                                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'shrink-0 text-xs font-medium',
                                    'text-rose-700' => $row['overdue'] ?? false,
                                    'text-amber-700' => ! ($row['overdue'] ?? false) && ($row['timing'] ?? '') === __('Expected today'),
                                    'text-slate-600' => ! ($row['overdue'] ?? false) && ($row['timing'] ?? '') !== __('Expected today'),
                                ]); ?>"><?php echo e($row['timing']); ?></span>
                            </a>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
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
        <?php endif; ?>

        <?php if(count($issuePipeline ?? []) > 0): ?>
            <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['padding' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
                <div class="border-b border-erp-border px-4 py-3">
                    <h2 class="text-sm font-semibold text-slate-900"><?php echo e(__('Pending issues')); ?></h2>
                </div>
                <ul class="divide-y divide-slate-100">
                    <?php $__currentLoopData = $issuePipeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>
                            <a
                                href="<?php echo e($row['url']); ?>"
                                class="flex items-center justify-between gap-3 px-4 py-3 text-sm transition hover:bg-slate-50"
                                <?php if($row['modal'] ?? false): ?> data-erp-modal-open <?php endif; ?>
                            >
                                <span class="min-w-0">
                                    <span class="block font-medium text-slate-900"><?php echo e($row['label']); ?></span>
                                    <span class="block truncate text-xs text-slate-500"><?php echo e($row['item']); ?></span>
                                </span>
                                <span class="shrink-0 text-xs font-medium text-slate-600"><?php echo e($row['status']); ?></span>
                            </a>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
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
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\store\desk\partials\pipelines.blade.php ENDPATH**/ ?>
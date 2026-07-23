<?php
    $allocation = $tabData['allocation'] ?? null;
    $loss = $tabData['loss_metrics'] ?? [];
    $nextPreview = $tabData['next_range_preview'] ?? null;
?>

<?php if($allocation): ?>
    <div class="mb-6 rounded-xl border-2 border-erp-primary bg-erp-primary/5 p-6">
        <h3 class="text-lg font-bold text-erp-primary"><?php echo e(__('Serial Allocation')); ?></h3>
        <p class="mt-2 text-3xl font-bold tabular-nums tracking-tight text-slate-900">
            <?php echo e($allocation->formatSerial($allocation->serial_start)); ?>

            <span class="text-slate-400">—</span>
            <?php echo e($allocation->formatSerial($allocation->serial_end)); ?>

        </p>
        <p class="mt-1 text-sm text-slate-600"><?php echo e(__('Quantity')); ?>: <?php echo e($allocation->allocatedQuantity()); ?></p>
        <dl class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-3">
            <div>
                <dt class="text-slate-500"><?php echo e(__('Prefix')); ?></dt>
                <dd class="font-medium"><?php echo e($allocation->serial_prefix !== '' ? $allocation->serial_prefix : __('—')); ?></dd>
            </div>
            <div>
                <dt class="text-slate-500"><?php echo e(__('Padding')); ?></dt>
                <dd class="font-medium"><?php echo e($allocation->serial_padding_length); ?></dd>
            </div>
            <div>
                <dt class="text-slate-500"><?php echo e(__('Allocated start')); ?></dt>
                <dd class="font-medium tabular-nums"><?php echo e($allocation->formatSerial($allocation->serial_start)); ?></dd>
            </div>
            <div>
                <dt class="text-slate-500"><?php echo e(__('Allocated end')); ?></dt>
                <dd class="font-medium tabular-nums"><?php echo e($allocation->formatSerial($allocation->serial_end)); ?></dd>
            </div>
            <?php if($nextPreview): ?>
                <div class="sm:col-span-2">
                    <dt class="text-slate-500"><?php echo e(__('Next expected range preview')); ?></dt>
                    <dd class="font-medium tabular-nums"><?php echo e($nextPreview['start']); ?> — <?php echo e($nextPreview['end']); ?></dd>
                </div>
            <?php endif; ?>
        </dl>
        <?php if($allocation->is_confirmed): ?>
            <p class="mt-2 text-sm text-emerald-700">
                <?php echo e(__('Confirmed')); ?> <?php echo e($allocation->confirmed_at?->format('Y-m-d H:i')); ?>

                <?php if($allocation->confirmedByUser): ?> — <?php echo e($allocation->confirmedByUser->name); ?> <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>

    <?php if(($tabData['can_confirm'] ?? false) && ! $allocation->is_confirmed): ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6']); ?>
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Confirm Production')); ?></h3>
            <form method="POST" action="<?php echo e(route('admin.production.job-cards.serials.confirm', $jobCard)); ?>" class="grid grid-cols-1 gap-4 md:grid-cols-3 max-w-3xl">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="erp-label"><?php echo e(__('Produced (last serial number)')); ?></label>
                    <input type="number" name="produced_end" class="erp-input w-full" min="<?php echo e($allocation->serial_start); ?>" max="<?php echo e($allocation->serial_end); ?>" required>
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('Spoiled start')); ?></label>
                    <input type="number" name="spoiled_start" class="erp-input w-full" min="<?php echo e($allocation->serial_start); ?>" max="<?php echo e($allocation->serial_end); ?>">
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('Spoiled end')); ?></label>
                    <input type="number" name="spoiled_end" class="erp-input w-full" min="<?php echo e($allocation->serial_start); ?>" max="<?php echo e($allocation->serial_end); ?>">
                </div>
                <div class="md:col-span-3">
                    <button type="submit" class="erp-btn-primary"><?php echo e(__('Confirm serial production')); ?></button>
                </div>
            </form>
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

    <?php if($allocation->is_confirmed): ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6']); ?>
            <dl class="grid grid-cols-1 gap-4 text-sm md:grid-cols-3">
                <div>
                    <dt class="text-slate-500"><?php echo e(__('Produced through')); ?></dt>
                    <dd class="font-medium tabular-nums"><?php echo e($allocation->produced_end ? $allocation->formatSerial($allocation->produced_end) : '—'); ?></dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e(__('Spoiled quantity')); ?></dt>
                    <dd class="font-medium text-red-700"><?php echo e($allocation->spoiled_quantity); ?></dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e(__('Production loss (auditable)')); ?></dt>
                    <dd class="font-medium"><?php echo e($loss['production_loss_quantity'] ?? 0); ?></dd>
                </div>
            </dl>
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

    <?php if(($tabData['spoiled_ranges'] ?? collect())->isNotEmpty()): ?>
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
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Spoiled Serial Ranges')); ?></h3>
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th><?php echo e(__('Range')); ?></th>
                        <th><?php echo e(__('Quantity')); ?></th>
                        <th><?php echo e(__('Recorded')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $tabData['spoiled_ranges']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $range): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="tabular-nums"><?php echo e($range->serial_start); ?> – <?php echo e($range->serial_end); ?></td>
                            <td><?php echo e($range->quantity); ?></td>
                            <td><?php echo e($range->recorded_at?->format('Y-m-d H:i')); ?> <?php if($range->recordedByUser): ?> (<?php echo e($range->recordedByUser->name); ?>) <?php endif; ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
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
<?php else: ?>
    <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['title' => __('No serial allocation'),'description' => __('This job card product does not use serial numbers.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No serial allocation')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('This job card product does not use serial numbers.'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\tabs\serials.blade.php ENDPATH**/ ?>
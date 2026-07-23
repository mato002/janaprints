<?php if(count($workQueue['items'] ?? []) > 0): ?>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4']); ?>
        <div class="border-b border-erp-border px-4 py-3">
            <h2 class="text-sm font-semibold text-slate-900"><?php echo e(__("Today's store queue")); ?></h2>
            <p class="mt-0.5 text-xs text-slate-500"><?php echo e(__('Receipts, issues, and counts waiting for you.')); ?></p>
        </div>
        <ul class="divide-y divide-slate-100">
            <?php $__currentLoopData = $workQueue['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $toneClasses = match ($item['tone'] ?? 'slate') {
                        'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                        'rose' => 'border-rose-200 bg-rose-50 text-rose-800',
                        'blue' => 'border-blue-200 bg-blue-50 text-blue-800',
                        default => 'border-slate-200 bg-slate-50 text-slate-800',
                    };
                    $kindLabel = match ($item['kind'] ?? '') {
                        'receipt' => __('Receive'),
                        'issue' => __('Issue'),
                        'count' => __('Count'),
                        default => __('Task'),
                    };
                ?>
                <li class="px-4 py-3">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <div class="mb-1 flex flex-wrap items-center gap-2">
                                <span class="inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide <?php echo e($toneClasses); ?>"><?php echo e($kindLabel); ?></span>
                                <span class="font-mono text-xs font-medium text-slate-700"><?php echo e($item['label']); ?></span>
                                <span class="text-xs font-medium <?php echo e(($item['status'] ?? '') === __('Due now') ? 'text-amber-700' : 'text-slate-500'); ?>"><?php echo e($item['status']); ?></span>
                            </div>
                            <p class="font-medium text-slate-900"><?php echo e($item['title']); ?></p>
                            <?php if(! empty($item['meta'])): ?>
                                <p class="text-xs text-slate-500"><?php echo e($item['meta']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                            <a
                                href="<?php echo e($item['url']); ?>"
                                class="erp-btn-secondary text-xs"
                                <?php if($item['modal'] ?? false): ?> data-erp-modal-open <?php else: ?> data-turbo-frame="erp-main" <?php endif; ?>
                            ><?php echo e(__('Review')); ?></a>
                            <?php if(($item['can_post'] ?? false) && ! empty($item['post_url'])): ?>
                                <form method="POST" action="<?php echo e($item['post_url']); ?>" class="inline" data-erp-desk-form>
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="from" value="store-desk">
                                    <button type="submit" class="erp-btn-primary text-xs"><?php echo e(__('Post to stock')); ?></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\store\desk\partials\work-queue.blade.php ENDPATH**/ ?>
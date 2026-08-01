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
        <h2 class="mb-2 text-sm font-semibold text-slate-900"><?php echo e(__('Needs attention')); ?></h2>
        <ul class="divide-y divide-slate-100">
            <?php $__currentLoopData = $workQueue['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $toneClasses = match ($item['tone'] ?? 'slate') {
                        'amber' => 'border-amber-200 bg-amber-50 text-amber-900',
                        'indigo' => 'border-indigo-200 bg-indigo-50 text-indigo-900',
                        'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
                        'rose' => 'border-rose-200 bg-rose-50 text-rose-900',
                        default => 'border-slate-200 bg-slate-50 text-slate-800',
                    };
                    $kindLabel = match ($item['kind'] ?? '') {
                        'quote_request' => __('Lead'),
                        'quotation' => __('Quote'),
                        'release' => __('Release'),
                        'draft_quote' => __('Draft'),
                        'follow_up' => __('Follow-up'),
                        default => __('Task'),
                    };
                ?>
                <li>
                    <a
                        href="<?php echo e($item['url']); ?>"
                        class="<?php echo \Illuminate\Support\Arr::toCssClasses(['flex items-center justify-between gap-3 px-1 py-2.5 text-sm transition hover:bg-slate-50']); ?>"
                        <?php if($item['modal'] ?? false): ?> data-erp-modal-open <?php else: ?> data-turbo-frame="erp-main" <?php endif; ?>
                    >
                        <span class="min-w-0">
                            <span class="mb-0.5 inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide <?php echo e($toneClasses); ?>"><?php echo e($kindLabel); ?></span>
                            <span class="block truncate font-medium text-slate-900"><?php echo e($item['label']); ?></span>
                            <?php if(! empty($item['meta'])): ?>
                                <span class="block truncate text-xs text-slate-500"><?php echo e($item['meta']); ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="shrink-0 text-xs font-medium text-erp-accent"><?php echo e(__('Open')); ?></span>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/sales/desk/partials/work-queue.blade.php ENDPATH**/ ?>
<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $title] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $title,'description' => $description]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($description)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $attributes = $__attributesOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__attributesOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $component = $__componentOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__componentOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>

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
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-erp-border text-left text-[11px] uppercase tracking-wide text-slate-500">
                        <th class="px-3 py-2 font-semibold"><?php echo e(__('Report')); ?></th>
                        <th class="px-3 py-2 font-semibold"><?php echo e(__('Tab')); ?></th>
                        <th class="px-3 py-2 font-semibold"><?php echo e(__('Format')); ?></th>
                        <th class="px-3 py-2 font-semibold"><?php echo e(__('Status')); ?></th>
                        <th class="px-3 py-2 font-semibold"><?php echo e(__('Requested By')); ?></th>
                        <th class="px-3 py-2 font-semibold"><?php echo e(__('Queued')); ?></th>
                        <th class="px-3 py-2 font-semibold"><?php echo e(__('Rows')); ?></th>
                        <th class="px-3 py-2 font-semibold"><?php echo e(__('Action')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $exports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $export): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-b border-erp-border/60" data-export-id="<?php echo e($export->id); ?>">
                            <td class="px-3 py-2 font-medium text-erp-primary"><?php echo e($export->moduleLabel()); ?></td>
                            <td class="px-3 py-2 text-slate-600"><?php echo e(ucfirst(str_replace('_', ' ', $export->tab))); ?></td>
                            <td class="px-3 py-2 uppercase text-slate-600"><?php echo e($export->format); ?></td>
                            <td class="px-3 py-2">
                                <?php echo $__env->make('admin.commercial.reports.exports.partials.status-badge', ['export' => $export], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </td>
                            <td class="px-3 py-2 text-slate-600"><?php echo e($export->user?->name ?? '—'); ?></td>
                            <td class="px-3 py-2 text-slate-600"><?php echo e($export->queued_at?->format('Y-m-d H:i') ?? '—'); ?></td>
                            <td class="px-3 py-2 text-slate-600"><?php echo e($export->row_count ?? '—'); ?></td>
                            <td class="px-3 py-2">
                                <?php if($can_download && $export->isDownloadable()): ?>
                                    <a href="<?php echo e(route('admin.commercial.reports.exports.download', $export)); ?>" class="erp-btn-primary text-xs">
                                        <?php echo e(__('Download')); ?>

                                    </a>
                                <?php elseif($export->status->value === 'failed'): ?>
                                    <span class="text-xs text-erp-danger"><?php echo e(Str::limit($export->error_message, 40)); ?></span>
                                <?php elseif($export->isExpired()): ?>
                                    <span class="text-xs text-slate-500"><?php echo e(__('Expired')); ?></span>
                                <?php elseif(in_array($export->status->value, ['queued', 'processing'], true)): ?>
                                    <span class="text-xs text-slate-500 export-pending" data-status-url="<?php echo e(route('admin.commercial.reports.exports.status', $export)); ?>"><?php echo e(__('Processing…')); ?></span>
                                <?php else: ?>
                                    <span class="text-xs text-slate-500">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="px-3 py-8 text-center text-slate-500"><?php echo e(__('No exports yet. Use Export on any commercial report workspace.')); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($exports->hasPages()): ?>
            <div class="mt-4">
                <?php echo e($exports->links()); ?>

            </div>
        <?php endif; ?>
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

    <?php if($exports->contains(fn ($export) => in_array($export->status->value, ['queued', 'processing'], true))): ?>
        <script>
            (function () {
                const pending = document.querySelectorAll('.export-pending');
                pending.forEach((el) => {
                    const url = el.dataset.statusUrl;
                    const poll = () => {
                        fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                            .then((r) => r.json())
                            .then((data) => {
                                if (data.ready || data.failed || data.expired) {
                                    window.location.reload();
                                    return;
                                }
                                window.setTimeout(poll, 3000);
                            })
                            .catch(() => window.setTimeout(poll, 5000));
                    };
                    window.setTimeout(poll, 3000);
                });
            })();
        </script>
    <?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $attributes = $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $component = $__componentOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\commercial\reports\exports\index.blade.php ENDPATH**/ ?>
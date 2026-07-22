<?php if(count($fastActions ?? []) > 0): ?>
    <div class="mb-4">
        <h2 class="mb-2 text-sm font-semibold text-slate-900"><?php echo e(__('Quick actions')); ?></h2>
        <div class="flex flex-wrap gap-2">
            <?php $__currentLoopData = $fastActions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a
                    href="<?php echo e($action['url']); ?>"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold transition',
                        'border-erp-accent bg-erp-accent text-white hover:bg-erp-accent/90' => $action['primary'] ?? false,
                        'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' => ! ($action['primary'] ?? false),
                    ]); ?>"
                    <?php if($action['modal'] ?? false): ?> data-erp-modal-open <?php else: ?> data-turbo-frame="_top" <?php endif; ?>
                ><?php echo e($action['label']); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/store/desk/partials/fast-actions.blade.php ENDPATH**/ ?>
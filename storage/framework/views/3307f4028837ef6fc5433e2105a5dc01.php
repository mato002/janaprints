<?php if (isset($component)) { $__componentOriginald3ad0f200dc20b794011e332a16c068d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3ad0f200dc20b794011e332a16c068d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.modal-form','data' => ['title' => $receipt->receipt_number,'maxWidth' => '4xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.modal-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($receipt->receipt_number),'maxWidth' => '4xl']); ?>
    <div class="space-y-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="erp-badge"><?php echo e($receipt->status->value); ?></span>
            <span class="text-sm text-slate-600"><?php echo e($receipt->warehouse?->name); ?> · <?php echo e($receipt->receipt_date->format('d M Y')); ?></span>
        </div>

        <div class="rounded-lg border border-erp-border bg-white p-4">
            <h3 class="mb-2 text-sm font-semibold text-slate-900"><?php echo e(__('Line items')); ?></h3>
            <?php $__currentLoopData = $receipt->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="border-b border-slate-100 py-2 text-sm last:border-0">
                    <span class="font-medium"><?php echo e($line->inventoryItem?->item_name ?? '—'); ?></span>
                    <span class="text-slate-500"> — <?php echo e(number_format((float) $line->quantity, 2)); ?> @ <?php echo e(number_format((float) $line->unit_cost, 2)); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('post', $receipt)): ?>
            <form method="POST" action="<?php echo e(route('admin.inventory.receipts.post', $receipt)); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="from" value="store-desk">
                <button type="submit" class="erp-btn-primary text-sm"><?php echo e(__('Post to stock')); ?></button>
            </form>
        <?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald3ad0f200dc20b794011e332a16c068d)): ?>
<?php $attributes = $__attributesOriginald3ad0f200dc20b794011e332a16c068d; ?>
<?php unset($__attributesOriginald3ad0f200dc20b794011e332a16c068d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald3ad0f200dc20b794011e332a16c068d)): ?>
<?php $component = $__componentOriginald3ad0f200dc20b794011e332a16c068d; ?>
<?php unset($__componentOriginald3ad0f200dc20b794011e332a16c068d); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\store\desk\receipt-modal.blade.php ENDPATH**/ ?>
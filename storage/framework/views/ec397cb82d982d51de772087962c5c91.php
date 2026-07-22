<?php if (isset($component)) { $__componentOriginald3ad0f200dc20b794011e332a16c068d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3ad0f200dc20b794011e332a16c068d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.modal-form','data' => ['title' => $invoice->invoice_number,'maxWidth' => '2xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.modal-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoice->invoice_number),'maxWidth' => '2xl']); ?>
    <div class="space-y-4">
        <dl class="grid grid-cols-2 gap-3 text-sm">
            <div><dt class="text-slate-500"><?php echo e(__('Customer')); ?></dt><dd class="font-medium"><?php echo e($invoice->customer?->company_name ?? '—'); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Status')); ?></dt><dd><?php echo e(str_replace('_', ' ', $invoice->status->value)); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Total')); ?></dt><dd class="font-mono font-medium"><?php echo e(number_format((float) $invoice->total_amount, 2)); ?></dd></div>
            <div><dt class="text-slate-500"><?php echo e(__('Balance due')); ?></dt><dd class="font-mono"><?php echo e(number_format((float) $invoice->balance_due, 2)); ?></dd></div>
        </dl>

        <div class="flex flex-wrap gap-2">
            <a href="<?php echo e(route('admin.invoices.document', $invoice)); ?>" class="erp-btn-primary text-sm" target="_blank" rel="noopener"><?php echo e(__('Print invoice')); ?></a>
            <?php if($invoice->salesOrder): ?>
                <a href="<?php echo e(route('admin.sales.desk', ['customer' => $invoice->customer?->getRouteKey(), 'order' => $invoice->salesOrder->getRouteKey(), 'step' => 4])); ?>" class="erp-btn-secondary text-sm" data-turbo-frame="_top"><?php echo e(__('Back to desk')); ?></a>
            <?php endif; ?>
        </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\desk\invoice-show-modal.blade.php ENDPATH**/ ?>
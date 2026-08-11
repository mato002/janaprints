<?php if (isset($component)) { $__componentOriginald3ad0f200dc20b794011e332a16c068d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3ad0f200dc20b794011e332a16c068d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.modal-form','data' => ['title' => __('Material shortages'),'maxWidth' => '3xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.modal-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Material shortages')),'maxWidth' => '3xl']); ?>
    <div class="space-y-4">
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
            <p class="font-medium"><?php echo e(__('This job cannot enter the production queue until required stock is available.')); ?></p>
            <p class="mt-1"><?php echo e(__('Order :order · Job :job', [
                'order' => $salesOrder->order_number,
                'job' => $jobCard->job_card_number,
            ])); ?></p>
        </div>

        <?php if(! ($materials['ready'] ?? false) && ! empty($materials['missing'])): ?>
            <div>
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Short materials')); ?></h3>
                <ul class="space-y-2 text-sm">
                    <?php $__currentLoopData = $materials['missing']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $qty = rtrim(rtrim(number_format($line['shortfall'], 3, '.', ''), '0'), '.');
                            $unit = $line['unit'] ? ' '.$line['unit'] : '';
                        ?>
                        <li class="rounded-lg border border-slate-200 bg-white px-3 py-2">
                            <span class="font-medium text-slate-900"><?php echo e($line['item']); ?></span>
                            <span class="text-slate-600"> — <?php echo e(__('Need :qty:unit more in stock', ['qty' => $qty, 'unit' => $unit])); ?></span>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php else: ?>
            <p class="text-sm text-slate-600"><?php echo e($materials['detail'] ?? __('Material readiness could not be assessed.')); ?></p>
        <?php endif; ?>

        <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
            <p class="font-medium text-slate-900"><?php echo e(__('Who resolves this?')); ?></p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                <?php if($canReceiveStock): ?>
                    <li><?php echo e(__('Receive the missing items into warehouse stock (Inventory → Stock receipts).')); ?></li>
                <?php else: ?>
                    <li><?php echo e(__('Ask the storekeeper to receive the missing items into warehouse stock.')); ?></li>
                <?php endif; ?>
                <?php if($canReserveMaterials): ?>
                    <li><?php echo e(__('Open the job card Materials tab and reserve stock against this job.')); ?></li>
                <?php else: ?>
                    <li><?php echo e(__('Ask production or inventory staff to reserve stock on the job card Materials tab.')); ?></li>
                <?php endif; ?>
                <li><?php echo e(__('Return here and submit to the production queue once Materials shows ready.')); ?></li>
            </ul>
        </div>

        <div class="flex flex-wrap gap-2">
            <?php if($canOpenJobCard && ($materials['materials_url'] ?? null)): ?>
                <a
                    href="<?php echo e($materials['materials_url']); ?>"
                    class="erp-btn-primary text-sm"
                    data-turbo-frame="erp-main"
                    data-erp-form-modal-close
                ><?php echo e(__('Open job card materials')); ?></a>
            <?php endif; ?>
            <?php if($canReceiveStock): ?>
                <a
                    href="<?php echo e(route('admin.inventory.receipts.index')); ?>"
                    class="erp-btn-secondary text-sm"
                    data-turbo-frame="erp-main"
                    data-erp-form-modal-close
                ><?php echo e(__('Go to stock receipts')); ?></a>
            <?php endif; ?>
            <button type="button" class="erp-btn-secondary text-sm" data-erp-form-modal-close><?php echo e(__('Close')); ?></button>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/sales/desk/materials-modal.blade.php ENDPATH**/ ?>
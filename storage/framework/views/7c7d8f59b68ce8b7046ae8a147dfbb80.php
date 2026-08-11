<?php if (isset($component)) { $__componentOriginald3ad0f200dc20b794011e332a16c068d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3ad0f200dc20b794011e332a16c068d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.modal-form','data' => ['title' => __('Production materials'),'maxWidth' => '3xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.modal-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Production materials')),'maxWidth' => '3xl']); ?>
    <div class="space-y-4">
        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'rounded-lg border px-4 py-3 text-sm',
            'border-rose-200 bg-rose-50 text-rose-900' => $issueType !== 'ready',
            'border-emerald-200 bg-emerald-50 text-emerald-900' => $issueType === 'ready',
        ]); ?>">
            <p class="font-medium"><?php echo e(__('Order :order', ['order' => $salesOrder->order_number])); ?></p>
            <?php if($productName): ?>
                <p class="mt-1"><?php echo e(__('Product: :product', ['product' => $productName])); ?></p>
            <?php endif; ?>
            <?php if($jobCard): ?>
                <p class="mt-1"><?php echo e(__('Job: :job', ['job' => $jobCard->job_card_number])); ?></p>
            <?php endif; ?>
        </div>

        <?php if($issueType === 'bom'): ?>
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                <p class="font-medium"><?php echo e(__('No bill of materials configured')); ?></p>
                <p class="mt-2"><?php echo e(__('This product does not have a BOM yet, so the system cannot calculate raw materials or check stock. Production must configure the BOM before this order can be released.')); ?></p>
            </div>
        <?php elseif($issueType === 'no_product'): ?>
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                <p class="font-medium"><?php echo e(__('No inventory product linked')); ?></p>
                <p class="mt-2"><?php echo e(__('Link a catalogue product to this order so material requirements can be generated.')); ?></p>
            </div>
        <?php elseif($issueType === 'shortage' && ! empty($materials['missing'])): ?>
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
        <?php elseif($issueType === 'requirements'): ?>
            <p class="text-sm text-slate-700"><?php echo e(__('Material requirements have not been generated for this job yet.')); ?></p>
        <?php else: ?>
            <p class="text-sm text-slate-700"><?php echo e($materials['detail'] ?? __('Material readiness could not be assessed.')); ?></p>
        <?php endif; ?>

        <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
            <p class="font-medium text-slate-900"><?php echo e(__('What happens next')); ?></p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                <?php if($issueType === 'bom'): ?>
                    <?php if($canManageBom): ?>
                        <li><?php echo e(__('Create or activate a BOM for this finished product in Production.')); ?></li>
                    <?php else: ?>
                        <li><?php echo e(__('Ask production to create a BOM for this product.')); ?></li>
                    <?php endif; ?>
                    <?php if($canEditOrder): ?>
                        <li><?php echo e(__('Or edit the order if the wrong product was selected.')); ?></li>
                    <?php endif; ?>
                <?php elseif($issueType === 'no_product'): ?>
                    <?php if($canEditOrder): ?>
                        <li><?php echo e(__('Edit the order and link the correct catalogue product.')); ?></li>
                    <?php else: ?>
                        <li><?php echo e(__('Ask sales support to link the correct catalogue product on the order.')); ?></li>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if($canReceiveStock): ?>
                        <li><?php echo e(__('Receive missing stock through Inventory → Stock receipts.')); ?></li>
                    <?php else: ?>
                        <li><?php echo e(__('Ask the storekeeper to receive the missing stock.')); ?></li>
                    <?php endif; ?>
                    <?php if($canReserveMaterials): ?>
                        <li><?php echo e(__('Reserve stock on the job card Materials tab once stock is available.')); ?></li>
                    <?php else: ?>
                        <li><?php echo e(__('Ask production or inventory staff to reserve stock on the job card.')); ?></li>
                    <?php endif; ?>
                <?php endif; ?>
                <li><?php echo e(__('Use Save & continue later on the sales desk — this order stays in Needs attention until you return.')); ?></li>
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
            <?php if($canEditOrder): ?>
                <a
                    href="<?php echo e(route('admin.sales-orders.edit', [$salesOrder, 'from' => 'sales-desk'])); ?>"
                    class="erp-btn-secondary text-sm"
                    data-erp-modal-open
                ><?php echo e(__('Edit order')); ?></a>
            <?php endif; ?>
            <?php if($canManageBom): ?>
                <a
                    href="<?php echo e(route('admin.production.boms.index')); ?>"
                    class="erp-btn-secondary text-sm"
                    data-turbo-frame="erp-main"
                    data-erp-form-modal-close
                ><?php echo e(__('Manage BOMs')); ?></a>
            <?php endif; ?>
            <?php if($canReceiveStock): ?>
                <a
                    href="<?php echo e(route('admin.inventory.receipts.index')); ?>"
                    class="erp-btn-secondary text-sm"
                    data-turbo-frame="erp-main"
                    data-erp-form-modal-close
                ><?php echo e(__('Stock receipts')); ?></a>
            <?php endif; ?>
            <a
                href="<?php echo e($resumeUrl); ?>"
                class="erp-btn-secondary text-sm"
                data-turbo-frame="erp-main"
                data-erp-form-modal-close
            ><?php echo e(__('Back to walk-in')); ?></a>
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
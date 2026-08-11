<?php if(filled($quotation->estimation_version)): ?>
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
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="font-medium mb-1"><?php echo e(__('Printing Intelligence Estimate')); ?></h3>
                <p class="text-xs text-slate-500"><?php echo e(__('Advisory costing from Printing Intelligence — does not replace line-item totals above.')); ?></p>
            </div>
            <?php if($linkedArtworkAnalysis): ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('printing.intelligence.view')): ?>
                    <a href="<?php echo e(route('admin.printing-intelligence.artwork-analysis.show', $linkedArtworkAnalysis)); ?>" class="erp-btn-secondary text-xs"><?php echo e(__('Open in Printing Intelligence')); ?></a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <dl class="mt-4 grid grid-cols-2 gap-3 text-sm lg:grid-cols-4">
            <div><dt class="text-xs text-slate-500"><?php echo e(__('Estimated total cost')); ?></dt><dd><?php echo e($quotation->estimated_total_cost !== null ? number_format((float) $quotation->estimated_total_cost, 2) : '—'); ?></dd></div>
            <div><dt class="text-xs text-slate-500"><?php echo e(__('Recommended price')); ?></dt><dd><?php echo e($quotation->recommended_price !== null ? number_format((float) $quotation->recommended_price, 2) : '—'); ?></dd></div>
            <div><dt class="text-xs text-slate-500"><?php echo e(__('Confidence')); ?></dt><dd><?php echo e($quotation->confidence_score !== null ? number_format((float) $quotation->confidence_score, 1).'%' : '—'); ?></dd></div>
            <div><dt class="text-xs text-slate-500"><?php echo e(__('Estimation version')); ?></dt><dd><?php echo e($quotation->estimation_version ?? '—'); ?></dd></div>
            <?php if($appliedQuotationEstimate?->applied_at): ?>
                <div class="md:col-span-2"><dt class="text-xs text-slate-500"><?php echo e(__('Last applied')); ?></dt><dd><?php echo e($appliedQuotationEstimate->applied_at->format('Y-m-d H:i')); ?> <?php if($appliedQuotationEstimate->appliedByUser): ?> — <?php echo e($appliedQuotationEstimate->appliedByUser->name); ?> <?php endif; ?></dd></div>
            <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/sales/quotations/partials/printing-intelligence-estimate.blade.php ENDPATH**/ ?>
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
    <h3 class="font-medium mb-3"><?php echo e(__('Artwork')); ?></h3>

    <?php if($artworkLink['linked']): ?>
        <div class="mb-4 rounded-lg border border-erp-border bg-slate-50 p-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-slate-900"><?php echo e($artworkLink['linked']['title']); ?></p>
                    <p class="text-xs text-slate-500"><?php echo e($artworkLink['linked']['number']); ?></p>
                </div>
                <div class="flex items-center gap-2">
                    <?php if (isset($component)) { $__componentOriginal6b1e38fda99422b6943f33aba545ca7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.enum-status-badge','data' => ['status' => $artworkLink['linked']['status']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.enum-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($artworkLink['linked']['status'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6b1e38fda99422b6943f33aba545ca7b)): ?>
<?php $attributes = $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b; ?>
<?php unset($__attributesOriginal6b1e38fda99422b6943f33aba545ca7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6b1e38fda99422b6943f33aba545ca7b)): ?>
<?php $component = $__componentOriginal6b1e38fda99422b6943f33aba545ca7b; ?>
<?php unset($__componentOriginal6b1e38fda99422b6943f33aba545ca7b); ?>
<?php endif; ?>
                    <a href="<?php echo e($artworkLink['linked']['url']); ?>" class="erp-btn-ghost text-xs"><?php echo e(__('Open artwork')); ?></a>
                </div>
            </div>
            <?php if(! $artworkLink['linked']['is_approved']): ?>
                <p class="mt-2 text-sm text-amber-700"><?php echo e(__('Artwork must be approved before converting this quotation to a sales order.')); ?></p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <p class="mb-4 text-sm text-slate-600"><?php echo e(__('Link artwork from a print specification or an approved artwork request before converting to a sales order.')); ?></p>
    <?php endif; ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('linkArtwork', $quotation)): ?>
        <?php if($artworkLink['can_link'] && (count($artworkLink['library']) > 0 || count($artworkLink['requests']) > 0)): ?>
            <form method="POST" action="<?php echo e(route('admin.quotations.link-artwork', $quotation)); ?>" class="space-y-4" x-data="{ source: '<?php echo e(count($artworkLink['library']) > 0 ? 'library' : 'request'); ?>' }">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="erp-label"><?php echo e(__('Artwork source')); ?></label>
                    <select name="artwork_source" class="erp-input w-full max-w-md" x-model="source" required>
                        <?php if(count($artworkLink['library']) > 0): ?>
                            <option value="library"><?php echo e(__('Artwork version (print specification)')); ?></option>
                        <?php endif; ?>
                        <?php if(count($artworkLink['requests']) > 0): ?>
                            <option value="request"><?php echo e(__('Approved artwork request')); ?></option>
                        <?php endif; ?>
                    </select>
                </div>

                <?php if(count($artworkLink['library']) > 0): ?>
                    <div x-show="source === 'library'" x-cloak>
                        <?php echo $__env->make('admin.sales.quotations.partials.artwork-picker-field', [
                            'scopedCustomerId' => $quotation->customer_id,
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                <?php endif; ?>

                <?php if(count($artworkLink['requests']) > 0): ?>
                    <div x-show="source === 'request'" x-cloak>
                        <label class="erp-label"><?php echo e(__('Artwork request')); ?></label>
                        <select name="artwork_request_id" class="erp-input w-full max-w-xl" :required="source === 'request'">
                            <option value=""><?php echo e(__('Select artwork request')); ?></option>
                            <?php $__currentLoopData = $artworkLink['requests']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($item['id']); ?>"><?php echo e($item['label']); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="submit" class="erp-btn-primary"><?php echo e($artworkLink['linked'] ? __('Change linked artwork') : __('Link artwork')); ?></button>
                    <?php if($quotation->customer): ?>
                        <a href="<?php echo e(route('admin.crm.customers.show', ['customer' => $quotation->customer, 'tab' => 'print-specifications'])); ?>" class="erp-btn-ghost text-sm"><?php echo e(__('Manage print specifications')); ?></a>
                    <?php endif; ?>
                </div>
            </form>
        <?php elseif($artworkLink['can_link']): ?>
            <form method="POST" action="<?php echo e(route('admin.quotations.link-artwork', $quotation)); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="artwork_source" value="library">
                <?php if($quotation->customer): ?>
                    <?php echo $__env->make('admin.sales.quotations.partials.artwork-picker-field', [
                        'scopedCustomerId' => $quotation->customer_id,
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="submit" class="erp-btn-primary"><?php echo e(__('Link artwork')); ?></button>
                    </div>
                <?php else: ?>
                    <div class="rounded-lg border border-dashed border-erp-border p-4 text-sm text-slate-600">
                        <p><?php echo e(__('No artwork is available for this customer yet.')); ?></p>
                    </div>
                <?php endif; ?>
            </form>
        <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/sales/quotations/partials/artwork-link.blade.php ENDPATH**/ ?>
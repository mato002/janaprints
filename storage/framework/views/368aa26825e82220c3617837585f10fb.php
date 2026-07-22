<?php
    $value = $prefill ?? fn (string $field, mixed $default = null) => old($field, $specification?->{$field} ?? $default);
    $checked = fn (string $field) => (bool) old($field, $specification?->{$field} ?? ($templateDefaults[$field] ?? false));
?>

<?php if(! ($specification ?? null) && ($printTemplates ?? collect())->isNotEmpty()): ?>
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
        <h3 class="mb-3 font-medium"><?php echo e(__('Apply print product template')); ?></h3>
        <p class="mb-3 text-sm text-slate-600"><?php echo e(__('Select a template to pre-fill manufacturing defaults. All fields remain editable.')); ?></p>
        <select
            class="erp-input w-full max-w-xl"
            onchange="if (this.value) { window.location = '<?php echo e(route('admin.sales-orders.items.specification.create', [$salesOrder ?? request()->route('salesOrder'), $salesOrderItem ?? request()->route('salesOrderItem')])); ?>?template_id=' + this.value; }"
        >
            <option value=""><?php echo e(__('Choose template (optional)…')); ?></option>
            <?php $__currentLoopData = $printTemplates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tpl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($tpl->id); ?>" <?php if((string) ($selectedTemplateId ?? '') === (string) $tpl->id): echo 'selected'; endif; ?>><?php echo e($tpl->name); ?> (<?php echo e($tpl->code); ?>)</option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <input type="hidden" name="print_product_template_id" value="<?php echo e($value('print_product_template_id', $selectedTemplateId ?? null)); ?>">
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

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
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
        <h3 class="mb-4 font-medium"><?php echo e(__('Product')); ?></h3>
        <div class="space-y-3">
            <div>
                <label class="erp-label"><?php echo e(__('Production type')); ?></label>
                <select name="production_type" class="erp-input w-full">
                    <option value=""><?php echo e(__('Select…')); ?></option>
                    <?php $__currentLoopData = $productionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($type->value); ?>" <?php if($value('production_type') === $type->value): echo 'selected'; endif; ?>><?php echo e(str_replace('_', ' ', ucfirst($type->value))); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="erp-label"><?php echo e(__('Description')); ?></label>
                <textarea name="product_description" class="erp-input w-full" rows="2"><?php echo e($value('product_description')); ?></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="erp-label"><?php echo e(__('Quantity')); ?></label>
                    <input type="number" step="0.001" name="quantity" value="<?php echo e($value('quantity')); ?>" class="erp-input w-full">
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('Unit')); ?></label>
                    <input type="text" name="unit" value="<?php echo e($value('unit')); ?>" class="erp-input w-full" placeholder="copies">
                </div>
            </div>
            <div>
                <label class="erp-label"><?php echo e(__('Approval status')); ?></label>
                <select name="approval_status" class="erp-input w-full">
                    <?php $__currentLoopData = $approvalStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($status->value); ?>" <?php if($value('approval_status', 'draft') === $status->value): echo 'selected'; endif; ?>><?php echo e($status->label()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>
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
        <h3 class="mb-4 font-medium"><?php echo e(__('Dimensions')); ?></h3>
        <div class="grid grid-cols-2 gap-3">
            <div><label class="erp-label"><?php echo e(__('Size')); ?></label><input type="text" name="size" value="<?php echo e($value('size')); ?>" class="erp-input w-full"></div>
            <div><label class="erp-label"><?php echo e(__('Finished size')); ?></label><input type="text" name="finished_size" value="<?php echo e($value('finished_size')); ?>" class="erp-input w-full"></div>
            <div><label class="erp-label"><?php echo e(__('Sheet size')); ?></label><input type="text" name="sheet_size" value="<?php echo e($value('sheet_size')); ?>" class="erp-input w-full"></div>
            <div><label class="erp-label"><?php echo e(__('Orientation')); ?></label><input type="text" name="orientation" value="<?php echo e($value('orientation')); ?>" class="erp-input w-full" placeholder="portrait"></div>
        </div>
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
        <h3 class="mb-4 font-medium"><?php echo e(__('Materials')); ?></h3>
        <div class="space-y-3">
            <div>
                <label class="erp-label"><?php echo e(__('Paper')); ?></label>
                <select name="paper_inventory_item_id" class="erp-input w-full">
                    <option value=""><?php echo e(__('Select paper…')); ?></option>
                    <?php $__currentLoopData = $paperItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($item->id); ?>" <?php if((string) $value('paper_inventory_item_id') === (string) $item->id): echo 'selected'; endif; ?>><?php echo e($item->item_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="erp-label"><?php echo e(__('Material')); ?></label>
                <select name="material_inventory_item_id" class="erp-input w-full">
                    <option value=""><?php echo e(__('Select material…')); ?></option>
                    <?php $__currentLoopData = $materialItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($item->id); ?>" <?php if((string) $value('material_inventory_item_id') === (string) $item->id): echo 'selected'; endif; ?>><?php echo e($item->item_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>
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
        <h3 class="mb-4 font-medium"><?php echo e(__('Print')); ?></h3>
        <div class="space-y-3">
            <div>
                <label class="erp-label"><?php echo e(__('Ink type')); ?></label>
                <select name="ink_type" class="erp-input w-full">
                    <option value=""><?php echo e(__('Select…')); ?></option>
                    <?php $__currentLoopData = $inkTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ink): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($ink->value); ?>" <?php if($value('ink_type') === $ink->value): echo 'selected'; endif; ?>><?php echo e($ink->label()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="erp-label"><?php echo e(__('Ink profile')); ?></label>
                <select name="ink_profile_id" class="erp-input w-full">
                    <option value=""><?php echo e(__('Select…')); ?></option>
                    <?php $__currentLoopData = $inkProfiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $profile): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($profile->id); ?>" <?php if((string) $value('ink_profile_id') === (string) $profile->id): echo 'selected'; endif; ?>><?php echo e($profile->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="erp-label"><?php echo e(__('Colour mode')); ?></label><input type="text" name="colour_mode" value="<?php echo e($value('colour_mode')); ?>" class="erp-input w-full"></div>
                <div><label class="erp-label"><?php echo e(__('Sides')); ?></label><input type="text" name="sides" value="<?php echo e($value('sides')); ?>" class="erp-input w-full" placeholder="single / double"></div>
            </div>
        </div>
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
        <h3 class="mb-4 font-medium"><?php echo e(__('Finishing')); ?></h3>
        <div class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div><label class="erp-label"><?php echo e(__('Binding')); ?></label><input type="text" name="binding_type" value="<?php echo e($value('binding_type')); ?>" class="erp-input w-full"></div>
                <div><label class="erp-label"><?php echo e(__('Finishing type')); ?></label><input type="text" name="finishing_type" value="<?php echo e($value('finishing_type')); ?>" class="erp-input w-full"></div>
            </div>
            <div class="grid grid-cols-2 gap-2 text-sm">
                <?php $__currentLoopData = ['lamination', 'foiling', 'spot_uv', 'embossing', 'debossing', 'die_cutting', 'creasing', 'perforation', 'numbering_required', 'eyelets']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="<?php echo e($option); ?>" value="1" <?php if($checked($option)): echo 'checked'; endif; ?>>
                        <?php echo e(str_replace('_', ' ', ucfirst($option))); ?>

                    </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
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
        <h3 class="mb-4 font-medium"><?php echo e(__('Imposition')); ?></h3>
        <div class="grid grid-cols-3 gap-3">
            <div><label class="erp-label"><?php echo e(__('Ups')); ?></label><input type="number" min="1" name="ups" value="<?php echo e($value('ups')); ?>" class="erp-input w-full"></div>
            <div><label class="erp-label"><?php echo e(__('Estimated sheets')); ?></label><input type="number" min="0" name="estimated_sheets" value="<?php echo e($value('estimated_sheets')); ?>" class="erp-input w-full"></div>
            <div><label class="erp-label"><?php echo e(__('Waste %')); ?></label><input type="number" step="0.01" min="0" max="100" name="waste_allowance_percent" value="<?php echo e($value('waste_allowance_percent')); ?>" class="erp-input w-full"></div>
        </div>
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
        <h3 class="mb-4 font-medium"><?php echo e(__('Artwork & notes')); ?></h3>
        <div class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div><label class="erp-label"><?php echo e(__('Artwork reference')); ?></label><input type="text" name="artwork_reference" value="<?php echo e($value('artwork_reference')); ?>" class="erp-input w-full"></div>
                <div><label class="erp-label"><?php echo e(__('Artwork version')); ?></label><input type="text" name="artwork_version" value="<?php echo e($value('artwork_version')); ?>" class="erp-input w-full"></div>
            </div>
            <div><label class="erp-label"><?php echo e(__('Production notes')); ?></label><textarea name="production_notes" class="erp-input w-full" rows="2"><?php echo e($value('production_notes')); ?></textarea></div>
            <div><label class="erp-label"><?php echo e(__('Delivery notes')); ?></label><textarea name="delivery_notes" class="erp-input w-full" rows="2"><?php echo e($value('delivery_notes')); ?></textarea></div>
        </div>
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
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\specifications\partials\form-fields.blade.php ENDPATH**/ ?>
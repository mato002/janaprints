<?php
    $value = fn (string $field, mixed $default = null) => old($field, $template?->{$field} ?? $default);
    $checked = fn (string $field) => (bool) old($field, $template?->{$field} ?? false);
?>

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
        <h3 class="mb-4 font-medium"><?php echo e(__('General')); ?></h3>
        <div class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div><?php if (isset($component)) { $__componentOriginal6da14397ddf3530b276d246dba7e4584 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6da14397ddf3530b276d246dba7e4584 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.entity-code-input','data' => ['record' => $template,'erp' => true,'maxlength' => '40']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.entity-code-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['record' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($template),'erp' => true,'maxlength' => '40']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6da14397ddf3530b276d246dba7e4584)): ?>
<?php $attributes = $__attributesOriginal6da14397ddf3530b276d246dba7e4584; ?>
<?php unset($__attributesOriginal6da14397ddf3530b276d246dba7e4584); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6da14397ddf3530b276d246dba7e4584)): ?>
<?php $component = $__componentOriginal6da14397ddf3530b276d246dba7e4584; ?>
<?php unset($__componentOriginal6da14397ddf3530b276d246dba7e4584); ?>
<?php endif; ?></div>
                <div><label class="erp-label"><?php echo e(__('Name')); ?></label><input type="text" name="name" value="<?php echo e($value('name')); ?>" class="erp-input w-full" required></div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="erp-label"><?php echo e(__('Category')); ?></label>
                    <select name="category" class="erp-input w-full" required>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat->value); ?>" <?php if($value('category') === $cat->value): echo 'selected'; endif; ?>><?php echo e($cat->label()); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('Production type')); ?></label>
                    <select name="production_type" class="erp-input w-full">
                        <option value=""><?php echo e(__('Select…')); ?></option>
                        <?php $__currentLoopData = $productionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($type->value); ?>" <?php if($value('production_type') === $type->value): echo 'selected'; endif; ?>><?php echo e(str_replace('_', ' ', ucfirst($type->value))); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
            <div><label class="erp-label"><?php echo e(__('Description')); ?></label><textarea name="description" class="erp-input w-full" rows="2"><?php echo e($value('description')); ?></textarea></div>
            <?php if($template ?? null): ?>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" <?php if($checked('is_active')): echo 'checked'; endif; ?>> <?php echo e(__('Active')); ?></label>
            <?php endif; ?>
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
        <h3 class="mb-4 font-medium"><?php echo e(__('Manufacturing defaults')); ?></h3>
        <div class="grid grid-cols-2 gap-3">
            <div><label class="erp-label"><?php echo e(__('GSM')); ?></label><input type="text" name="gsm" value="<?php echo e($value('gsm')); ?>" class="erp-input w-full"></div>
            <div><label class="erp-label"><?php echo e(__('Size')); ?></label><input type="text" name="default_size" value="<?php echo e($value('default_size')); ?>" class="erp-input w-full"></div>
            <div><label class="erp-label"><?php echo e(__('Finished size')); ?></label><input type="text" name="default_finished_size" value="<?php echo e($value('default_finished_size')); ?>" class="erp-input w-full"></div>
            <div><label class="erp-label"><?php echo e(__('Sheet size')); ?></label><input type="text" name="default_sheet_size" value="<?php echo e($value('default_sheet_size')); ?>" class="erp-input w-full"></div>
            <div><label class="erp-label"><?php echo e(__('Orientation')); ?></label><input type="text" name="default_orientation" value="<?php echo e($value('default_orientation')); ?>" class="erp-input w-full"></div>
            <div><label class="erp-label"><?php echo e(__('Colour mode')); ?></label><input type="text" name="default_colour_mode" value="<?php echo e($value('default_colour_mode')); ?>" class="erp-input w-full" placeholder="4/4"></div>
            <div><label class="erp-label"><?php echo e(__('Colours')); ?></label><input type="number" min="1" max="16" name="number_of_colours" value="<?php echo e($value('number_of_colours')); ?>" class="erp-input w-full"></div>
            <div><label class="erp-label"><?php echo e(__('Sides')); ?></label><input type="text" name="default_sides" value="<?php echo e($value('default_sides')); ?>" class="erp-input w-full"></div>
            <div><label class="erp-label"><?php echo e(__('Binding')); ?></label><input type="text" name="default_binding_type" value="<?php echo e($value('default_binding_type')); ?>" class="erp-input w-full"></div>
            <div><label class="erp-label"><?php echo e(__('Finishing')); ?></label><input type="text" name="default_finishing_type" value="<?php echo e($value('default_finishing_type')); ?>" class="erp-input w-full"></div>
            <div><label class="erp-label"><?php echo e(__('Ups')); ?></label><input type="number" name="default_ups" value="<?php echo e($value('default_ups')); ?>" class="erp-input w-full"></div>
            <div><label class="erp-label"><?php echo e(__('Waste %')); ?></label><input type="number" step="0.01" name="default_waste_allowance_percent" value="<?php echo e($value('default_waste_allowance_percent')); ?>" class="erp-input w-full"></div>
        </div>
        <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
            <?php $__currentLoopData = ['default_lamination', 'default_foiling', 'default_spot_uv', 'default_embossing', 'default_debossing', 'default_die_cutting', 'default_creasing', 'default_perforation', 'default_numbering_required', 'default_eyelets']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="flex items-center gap-2"><input type="checkbox" name="<?php echo e($option); ?>" value="1" <?php if($checked($option)): echo 'checked'; endif; ?>> <?php echo e(str_replace(['default_', '_'], ['', ' '], ucfirst($option))); ?></label>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="mt-3 space-y-3">
            <div>
                <label class="erp-label"><?php echo e(__('Default paper')); ?></label>
                <select name="default_paper_inventory_item_id" class="erp-input w-full">
                    <option value=""><?php echo e(__('None')); ?></option>
                    <?php $__currentLoopData = $paperItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($item->id); ?>" <?php if((string) $value('default_paper_inventory_item_id') === (string) $item->id): echo 'selected'; endif; ?>><?php echo e($item->item_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div><label class="erp-label"><?php echo e(__('Default notes')); ?></label><textarea name="default_notes" class="erp-input w-full" rows="2"><?php echo e($value('default_notes')); ?></textarea></div>
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
        <h3 class="mb-4 font-medium"><?php echo e(__('Artwork guidance')); ?></h3>
        <div class="space-y-3">
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="artwork_required" value="1" <?php if(old('artwork_required', $template->artwork_required ?? true)): echo 'checked'; endif; ?>> <?php echo e(__('Artwork required')); ?></label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="bleed_required" value="1" <?php if($checked('bleed_required')): echo 'checked'; endif; ?>> <?php echo e(__('Bleed required')); ?></label>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="erp-label"><?php echo e(__('Safe margin')); ?></label><input type="text" name="safe_margin" value="<?php echo e($value('safe_margin')); ?>" class="erp-input w-full"></div>
                <div><label class="erp-label"><?php echo e(__('Resolution')); ?></label><input type="text" name="resolution_recommendation" value="<?php echo e($value('resolution_recommendation')); ?>" class="erp-input w-full"></div>
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
        <h3 class="mb-4 font-medium"><?php echo e(__('Routing recommendations')); ?></h3>
        <div class="space-y-3">
            <div>
                <label class="erp-label"><?php echo e(__('Preferred work center')); ?></label>
                <select name="preferred_work_center_id" class="erp-input w-full">
                    <option value=""><?php echo e(__('None')); ?></option>
                    <?php $__currentLoopData = $workCenters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($wc->id); ?>" <?php if((string) $value('preferred_work_center_id') === (string) $wc->id): echo 'selected'; endif; ?>><?php echo e($wc->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div><label class="erp-label"><?php echo e(__('Operator skill')); ?></label><input type="text" name="preferred_operator_skill" value="<?php echo e($value('preferred_operator_skill')); ?>" class="erp-input w-full"></div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="optional_outsource" value="1" <?php if($checked('optional_outsource')): echo 'checked'; endif; ?>> <?php echo e(__('Outsourcing optional')); ?></label>
            <div><label class="erp-label"><?php echo e(__('Recommended packaging')); ?></label><textarea name="recommended_packaging" class="erp-input w-full" rows="2"><?php echo e($value('recommended_packaging')); ?></textarea></div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\print-templates\partials\form-fields.blade.php ENDPATH**/ ?>
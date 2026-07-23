<?php ($m = $item ?? null); ?>
<?php ($fields = $formFields ?? []); ?>
<?php ($brandDisplay = old('brand_name', $m?->brand_name ?? $m?->brand?->name)); ?>

<?php if(($fields['inventory_category_id']['visible'] ?? true)): ?>
<?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'inventory_category_id','label' => __('Category'),'options' => $categories,'value' => old('inventory_category_id', $m?->inventory_category_id),'required' => ($fields['inventory_category_id']['required'] ?? true),'readonly' => ($fields['inventory_category_id']['read_only'] ?? false),'createRoute' => 'admin.inventory.catalogue.categories.quick-create','refreshRoute' => 'admin.lookups.categories','permission' => 'catalogue.create','modalTitle' => __('Create category'),'selectClass' => 'erp-input w-full','emptyOption' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'inventory_category_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Category')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($categories),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('inventory_category_id', $m?->inventory_category_id)),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['inventory_category_id']['required'] ?? true)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['inventory_category_id']['read_only'] ?? false)),'create-route' => 'admin.inventory.catalogue.categories.quick-create','refresh-route' => 'admin.lookups.categories','permission' => 'catalogue.create','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create category')),'select-class' => 'erp-input w-full','empty-option' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3)): ?>
<?php $attributes = $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3; ?>
<?php unset($__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3)): ?>
<?php $component = $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3; ?>
<?php unset($__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3); ?>
<?php endif; ?>
<?php endif; ?>

<?php ($subcategoryOptions = $subcategories->map(fn ($s) => ['value' => $s->id, 'label' => trim(($s->category?->name ? $s->category->name.' / ' : '').$s->name)])->values()); ?>

<?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'subcategory_id','label' => __('Subcategory'),'options' => $subcategoryOptions,'value' => old('subcategory_id', $m?->subcategory_id),'createRoute' => 'admin.inventory.catalogue.subcategories.quick-create','refreshRoute' => 'admin.lookups.subcategories','permission' => 'catalogue.create','modalTitle' => __('Create subcategory'),'optionLabelKey' => 'label','optionValueKey' => 'value','selectClass' => 'erp-input w-full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'subcategory_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Subcategory')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($subcategoryOptions),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('subcategory_id', $m?->subcategory_id)),'create-route' => 'admin.inventory.catalogue.subcategories.quick-create','refresh-route' => 'admin.lookups.subcategories','permission' => 'catalogue.create','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create subcategory')),'option-label-key' => 'label','option-value-key' => 'value','select-class' => 'erp-input w-full']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3)): ?>
<?php $attributes = $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3; ?>
<?php unset($__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3)): ?>
<?php $component = $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3; ?>
<?php unset($__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3); ?>
<?php endif; ?>

<div>
    <label class="erp-label"><?php echo e(__('Brand')); ?></label>
    <input name="brand_name" class="erp-input w-full" value="<?php echo e($brandDisplay); ?>" placeholder="<?php echo e(__('Enter brand name')); ?>">
</div>

<?php if(($fields['unit_of_measure_id']['visible'] ?? true)): ?>
<?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'unit_of_measure_id','label' => __('Unit'),'options' => $units,'value' => old('unit_of_measure_id', $m?->unit_of_measure_id),'required' => ($fields['unit_of_measure_id']['required'] ?? true),'readonly' => ($fields['unit_of_measure_id']['read_only'] ?? false),'createRoute' => 'admin.inventory.catalogue.uoms.quick-create','refreshRoute' => 'admin.lookups.uoms','permission' => 'catalogue.create','modalTitle' => __('Create unit of measure'),'selectClass' => 'erp-input w-full','emptyOption' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'unit_of_measure_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Unit')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($units),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('unit_of_measure_id', $m?->unit_of_measure_id)),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['unit_of_measure_id']['required'] ?? true)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['unit_of_measure_id']['read_only'] ?? false)),'create-route' => 'admin.inventory.catalogue.uoms.quick-create','refresh-route' => 'admin.lookups.uoms','permission' => 'catalogue.create','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create unit of measure')),'select-class' => 'erp-input w-full','empty-option' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3)): ?>
<?php $attributes = $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3; ?>
<?php unset($__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3)): ?>
<?php $component = $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3; ?>
<?php unset($__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3); ?>
<?php endif; ?>
<?php endif; ?>

<?php if(($fields['item_name']['visible'] ?? true)): ?>
<div><label class="erp-label"><?php echo e(__('Name')); ?></label><input name="item_name" class="erp-input w-full" value="<?php echo e(old('item_name', $m?->item_name ?? ($fields['item_name']['default'] ?? ''))); ?>" <?php if($fields['item_name']['required'] ?? true): echo 'required'; endif; ?> <?php if($fields['item_name']['read_only'] ?? false): echo 'readonly'; endif; ?>></div>
<?php endif; ?>

<div>
    <label class="erp-label"><?php echo e(__('Stock role')); ?></label>
    <select name="stock_role" class="erp-select w-full" required>
        <?php $__currentLoopData = $stockRoles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($role->value); ?>" <?php if(old('stock_role', $m?->stock_role?->value ?? 'raw_material') === $role->value): echo 'selected'; endif; ?>><?php echo e($role->label()); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Finished products must use Finished good. Raw paper/ink use Raw material.')); ?></p>
</div>

<?php if(($fields['description']['visible'] ?? true)): ?>
<div><label class="erp-label"><?php echo e(__('Description')); ?></label><textarea name="description" class="erp-input w-full" <?php if($fields['description']['required'] ?? false): echo 'required'; endif; ?> <?php if($fields['description']['read_only'] ?? false): echo 'readonly'; endif; ?>><?php echo e(old('description', $m?->description ?? ($fields['description']['default'] ?? ''))); ?></textarea></div>
<?php endif; ?>

<?php ($existingAttributes = $m?->attributeValues?->keyBy('item_attribute_id') ?? collect()); ?>
<?php if($attributes->isNotEmpty()): ?>
<div class="rounded-lg border border-erp-border p-4">
    <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Product attributes')); ?></h3>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <?php $__currentLoopData = $attributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attribute): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php ($current = $existingAttributes->get($attribute->id)); ?>
            <div>
                <label class="erp-label"><?php echo e($attribute->name); ?></label>
                <?php if($attribute->data_type === 'select'): ?>
                    <select name="attributes[<?php echo e($attribute->id); ?>]" class="erp-input w-full" <?php if($attribute->is_required): echo 'required'; endif; ?>>
                        <option value=""><?php echo e(__('Select')); ?></option>
                        <?php $__currentLoopData = $attribute->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($option->id); ?>" <?php if(old("attributes.{$attribute->id}", $current?->attribute_option_id) == $option->id): echo 'selected'; endif; ?>><?php echo e($option->label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                <?php else: ?>
                    <input name="attributes[<?php echo e($attribute->id); ?>]" type="<?php echo e($attribute->data_type === 'number' ? 'number' : 'text'); ?>" class="erp-input w-full" value="<?php echo e(old("attributes.{$attribute->id}", $current?->value)); ?>" <?php if($attribute->is_required): echo 'required'; endif; ?>>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php endif; ?>

<?php if(($fields['reorder_level']['visible'] ?? true) || ($fields['reorder_quantity']['visible'] ?? true) || ($fields['standard_cost']['visible'] ?? true)): ?>
<div class="grid grid-cols-3 gap-4">
    <?php if(($fields['reorder_level']['visible'] ?? true)): ?>
    <div><label class="erp-label"><?php echo e(__('Reorder level')); ?></label><input type="number" step="0.001" name="reorder_level" class="erp-input w-full" value="<?php echo e(old('reorder_level', $m?->reorder_level ?? ($fields['reorder_level']['default'] ?? 0))); ?>" <?php if($fields['reorder_level']['required'] ?? true): echo 'required'; endif; ?> <?php if($fields['reorder_level']['read_only'] ?? false): echo 'readonly'; endif; ?>></div>
    <?php endif; ?>
    <?php if(($fields['reorder_quantity']['visible'] ?? true)): ?>
    <div><label class="erp-label"><?php echo e(__('Reorder qty')); ?></label><input type="number" step="0.001" name="reorder_quantity" class="erp-input w-full" value="<?php echo e(old('reorder_quantity', $m?->reorder_quantity ?? ($fields['reorder_quantity']['default'] ?? 0))); ?>" <?php if($fields['reorder_quantity']['required'] ?? true): echo 'required'; endif; ?> <?php if($fields['reorder_quantity']['read_only'] ?? false): echo 'readonly'; endif; ?>></div>
    <?php endif; ?>
    <?php if(($fields['standard_cost']['visible'] ?? true)): ?>
    <div><label class="erp-label"><?php echo e(__('Standard cost')); ?></label><input type="number" step="0.01" name="standard_cost" class="erp-input w-full" value="<?php echo e(old('standard_cost', $m?->standard_cost ?? ($fields['standard_cost']['default'] ?? 0))); ?>" <?php if($fields['standard_cost']['required'] ?? true): echo 'required'; endif; ?> <?php if($fields['standard_cost']['read_only'] ?? false): echo 'readonly'; endif; ?>></div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php echo $__env->make('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => $m ?? null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="rounded-lg border border-erp-border p-4" x-data="{ serialEnabled: <?php echo \Illuminate\Support\Js::from((bool) old('uses_serial_numbers', $m?->uses_serial_numbers ?? false))->toHtml() ?> }">
    <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Serial numbers')); ?></h3>
    <label class="inline-flex items-center gap-2 text-sm mb-4">
        <input type="checkbox" name="uses_serial_numbers" value="1" x-model="serialEnabled" <?php if(old('uses_serial_numbers', $m?->uses_serial_numbers ?? false)): echo 'checked'; endif; ?>>
        <span><?php echo e(__('Uses serial numbers')); ?></span>
    </label>
    <div x-show="serialEnabled" x-cloak class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label class="erp-label"><?php echo e(__('Serial prefix')); ?></label>
            <input name="serial_prefix" class="erp-input w-full" value="<?php echo e(old('serial_prefix', $m?->serial_prefix)); ?>" placeholder="RB-">
            <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Example: RB-{NUMBER} → RB-000001')); ?></p>
        </div>
        <div>
            <label class="erp-label"><?php echo e(__('Padding length')); ?></label>
            <input type="number" name="serial_padding_length" class="erp-input w-full" min="1" max="12" value="<?php echo e(old('serial_padding_length', $m?->serial_padding_length ?? 6)); ?>">
        </div>
    </div>
</div>

<?php ($routeSteps = old('route_steps', $m?->productionRouteSteps?->map(fn ($s) => ['step_name' => $s->step_name, 'sequence' => $s->sequence, 'is_active' => $s->is_active, 'work_center_id' => $s->work_center_id])->values()->all() ?? [['step_name' => '', 'sequence' => 1, 'is_active' => true, 'work_center_id' => null]])); ?>
<?php ($workCenterOptions = ($workCenters ?? collect())->map(fn ($wc) => ['id' => $wc->id, 'label' => $wc->name])->values()); ?>
<div class="rounded-lg border border-erp-border p-4" x-data="{ steps: <?php echo \Illuminate\Support\Js::from($routeSteps)->toHtml() ?>, workCenters: <?php echo \Illuminate\Support\Js::from($workCenterOptions)->toHtml() ?> }">
    <div class="mb-3 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-900"><?php echo e(__('Default production steps')); ?></h3>
        <button type="button" class="erp-btn-ghost text-xs" @click="steps.push({ step_name: '', sequence: steps.length + 1, is_active: true, work_center_id: null })"><?php echo e(__('Add step')); ?></button>
    </div>
    <p class="mb-3 text-xs text-slate-500"><?php echo e(__('Route template copied to job cards at creation. Catalog edits do not affect active jobs.')); ?></p>
    <template x-for="(step, index) in steps" :key="index">
        <div class="mb-2 grid grid-cols-12 gap-2 items-end">
            <div class="col-span-1">
                <label class="erp-label text-xs"><?php echo e(__('Seq')); ?></label>
                <input type="number" class="erp-input w-full text-sm" :name="'route_steps[' + index + '][sequence]'" x-model.number="step.sequence" min="1">
            </div>
            <div class="col-span-5">
                <label class="erp-label text-xs"><?php echo e(__('Step name')); ?></label>
                <input type="text" class="erp-input w-full text-sm" :name="'route_steps[' + index + '][step_name]'" x-model="step.step_name" placeholder="<?php echo e(__('e.g. Printing')); ?>">
            </div>
            <div class="col-span-3">
                <label class="erp-label text-xs"><?php echo e(__('Work center')); ?></label>
                <select class="erp-input w-full text-sm" :name="'route_steps[' + index + '][work_center_id]'" x-model="step.work_center_id">
                    <option value=""><?php echo e(__('—')); ?></option>
                    <template x-for="wc in workCenters" :key="wc.id">
                        <option :value="wc.id" x-text="wc.label"></option>
                    </template>
                </select>
            </div>
            <div class="col-span-1">
                <label class="inline-flex items-center gap-1 text-xs">
                    <input type="checkbox" :name="'route_steps[' + index + '][is_active]'" value="1" x-model="step.is_active">
                    <?php echo e(__('Active')); ?>

                </label>
            </div>
            <div class="col-span-2">
                <button type="button" class="erp-btn-ghost text-xs text-red-700" @click="steps.splice(index, 1)"><?php echo e(__('Remove')); ?></button>
            </div>
        </div>
    </template>
</div>

<?php ($materialLines = old('material_requirements', ($productBomLines ?? collect())->map(fn ($l) => [
    'inventory_item_id' => $l->inventory_item_id !== null ? (string) $l->inventory_item_id : '',
    'quantity_per_unit' => (float) $l->quantity_per_unit,
    'quantity_formula' => $l->quantity_formula ?? '',
    'is_active' => (bool) ($l->is_active ?? true),
])->values()->all() ?: [['inventory_item_id' => '', 'quantity_per_unit' => 1, 'quantity_formula' => '', 'is_active' => true]])); ?>
<div class="rounded-lg border border-erp-border p-4" x-data="{ materials: <?php echo \Illuminate\Support\Js::from($materialLines)->toHtml() ?> }">
    <div class="mb-3 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-900"><?php echo e(__('Required materials')); ?></h3>
        <button type="button" class="erp-btn-ghost text-xs" @click="materials.push({ inventory_item_id: '', quantity_per_unit: 1, quantity_formula: '', is_active: true })"><?php echo e(__('Add material')); ?></button>
    </div>
    <p class="mb-3 text-xs text-slate-500"><?php echo e(__('Defines BOM for finished products. Formula examples: JOB_QTY * 0.01, JOB_QTY / 500, or fixed quantity.')); ?></p>
    <template x-for="(line, index) in materials" :key="index">
        <div class="mb-2 grid grid-cols-12 gap-2 items-end">
            <div class="col-span-4">
                <label class="erp-label text-xs"><?php echo e(__('Material')); ?></label>
                
                <select
                    class="erp-input w-full text-sm"
                    :name="'material_requirements[' + index + '][inventory_item_id]'"
                    x-model="line.inventory_item_id"
                >
                    <option value=""><?php echo e(__('Select')); ?></option>
                    <?php $__currentLoopData = $rawMaterials ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($rm->id); ?>"><?php echo e($rm->sku); ?> — <?php echo e($rm->item_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-span-2">
                <label class="erp-label text-xs"><?php echo e(__('Per unit')); ?></label>
                <input type="number" step="0.0001" class="erp-input w-full text-sm" :name="'material_requirements[' + index + '][quantity_per_unit]'" x-model.number="line.quantity_per_unit" min="0.0001">
            </div>
            <div class="col-span-3">
                <label class="erp-label text-xs"><?php echo e(__('Formula')); ?></label>
                <input type="text" class="erp-input w-full text-sm" :name="'material_requirements[' + index + '][quantity_formula]'" x-model="line.quantity_formula" placeholder="JOB_QTY * 0.01">
            </div>
            <div class="col-span-1">
                <label class="inline-flex items-center gap-1 text-xs">
                    <input type="checkbox" :name="'material_requirements[' + index + '][is_active]'" value="1" x-model="line.is_active">
                    <?php echo e(__('Active')); ?>

                </label>
            </div>
            <div class="col-span-2">
                <button type="button" class="erp-btn-ghost text-xs text-red-700" @click="materials.splice(index, 1)"><?php echo e(__('Remove')); ?></button>
            </div>
        </div>
    </template>
</div>

<?php ($qcLines = old('qc_checklist', ($productQcChecklistLines ?? collect())->map(fn ($l) => [
    'label' => $l->label,
    'is_active' => $l->is_active ?? true,
])->values()->all() ?: app(\App\Support\Production\ProductQcChecklistService::class)->defaultLinePayload()->all())); ?>
<div class="rounded-lg border border-erp-border p-4" x-data="{ qcItems: <?php echo \Illuminate\Support\Js::from($qcLines)->toHtml() ?> }">
    <div class="mb-3 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-900"><?php echo e(__('QC checklist')); ?></h3>
        <button type="button" class="erp-btn-ghost text-xs" @click="qcItems.push({ label: '', is_active: true })"><?php echo e(__('Add item')); ?></button>
    </div>
    <p class="mb-3 text-xs text-slate-500"><?php echo e(__('Inspection checklist snapshotted to job cards when sent to QC.')); ?></p>
    <label class="mb-3 inline-flex items-center gap-2 text-sm">
        <input type="checkbox" name="requires_customer_approval" value="1" <?php if(old('requires_customer_approval', $m?->requires_customer_approval ?? false)): echo 'checked'; endif; ?>>
        <?php echo e(__('Requires customer approval (large branding / signage / custom design)')); ?>

    </label>
    <template x-for="(item, index) in qcItems" :key="index">
        <div class="mb-2 grid grid-cols-12 gap-2 items-end">
            <div class="col-span-8">
                <label class="erp-label text-xs"><?php echo e(__('Check item')); ?></label>
                <input type="text" class="erp-input w-full text-sm" :name="'qc_checklist[' + index + '][label]'" x-model="item.label" required>
            </div>
            <div class="col-span-2">
                <label class="inline-flex items-center gap-1 text-xs">
                    <input type="checkbox" :name="'qc_checklist[' + index + '][is_active]'" value="1" x-model="item.is_active">
                    <?php echo e(__('Active')); ?>

                </label>
            </div>
            <div class="col-span-2">
                <button type="button" class="erp-btn-ghost text-xs text-red-700" @click="qcItems.splice(index, 1)"><?php echo e(__('Remove')); ?></button>
            </div>
        </div>
    </template>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\inventory\items\partials\form.blade.php ENDPATH**/ ?>
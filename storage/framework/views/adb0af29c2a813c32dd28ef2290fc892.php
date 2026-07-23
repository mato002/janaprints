<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'customer',
    'specification' => null,
    'serialProfile' => null,
    'serialSummary' => null,
    'statuses' => [],
    'billingTypes' => [],
    'fulfilmentMethods' => [],
    'artworkTypes' => [],
    'showArtworkUpload' => true,
    'defaultStatus' => 'draft',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'customer',
    'specification' => null,
    'serialProfile' => null,
    'serialSummary' => null,
    'statuses' => [],
    'billingTypes' => [],
    'fulfilmentMethods' => [],
    'artworkTypes' => [],
    'showArtworkUpload' => true,
    'defaultStatus' => 'draft',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $spec = $specification;
    $item = $spec?->inventoryItem;
    $statusValue = old('status', $spec?->status?->value ?? $defaultStatus);
?>

<div class="space-y-6">
    <?php if(! empty($liveReferenceWarnings)): ?>
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <?php $__currentLoopData = $liveReferenceWarnings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <p><?php echo e($warning); ?></p>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <section class="rounded-lg border border-erp-border p-4">
        <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Specification Details')); ?></h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="erp-label" for="name"><?php echo e(__('Name')); ?></label>
                <input id="name" name="name" class="erp-input w-full" required maxlength="255"
                    value="<?php echo e(old('name', $spec?->name)); ?>" placeholder="<?php echo e(__('e.g. Fortress Receipt Book')); ?>">
            </div>
            <div class="md:col-span-2">
                <label class="erp-label" for="description"><?php echo e(__('Description')); ?></label>
                <textarea id="description" name="description" class="erp-input w-full" rows="2"><?php echo e(old('description', $spec?->description)); ?></textarea>
            </div>
            <div>
                <label class="erp-label" for="status"><?php echo e(__('Status')); ?></label>
                <?php if($spec?->isReadOnly()): ?>
                    <input class="erp-input w-full bg-slate-50" readonly value="<?php echo e($spec->status->label()); ?>">
                <?php else: ?>
                    <select id="status" name="status" class="erp-input w-full" required>
                        <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($status->value); ?>" <?php if($statusValue === $status->value): echo 'selected'; endif; ?>>
                                <?php echo e($status->label()); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                <?php endif; ?>
            </div>
            <?php if($spec): ?>
                <div>
                    <label class="erp-label"><?php echo e(__('Specification code')); ?></label>
                    <input class="erp-input w-full bg-slate-50" readonly value="<?php echo e($spec->specification_code); ?>">
                </div>
            <?php endif; ?>
            <div>
                <label class="erp-label" for="default_quantity"><?php echo e(__('Default quantity')); ?></label>
                <input type="number" step="0.001" min="0" id="default_quantity" name="default_quantity" class="erp-input w-full"
                    value="<?php echo e(old('default_quantity', $spec?->default_quantity)); ?>">
            </div>
            <div>
                <label class="erp-label" for="default_unit_price"><?php echo e(__('Default unit price')); ?></label>
                <input type="number" step="0.01" min="0" id="default_unit_price" name="default_unit_price" class="erp-input w-full"
                    value="<?php echo e(old('default_unit_price', $spec?->default_unit_price)); ?>">
            </div>
            <div>
                <label class="erp-label" for="default_billing_type"><?php echo e(__('Default billing type')); ?></label>
                <select id="default_billing_type" name="default_billing_type" class="erp-input w-full">
                    <option value=""><?php echo e(__('—')); ?></option>
                    <?php $__currentLoopData = $billingTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($type->value); ?>" <?php if(old('default_billing_type', $spec?->default_billing_type?->value) === $type->value): echo 'selected'; endif; ?>>
                            <?php echo e($type->label()); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="erp-label" for="default_fulfilment_method"><?php echo e(__('Default fulfilment')); ?></label>
                <select id="default_fulfilment_method" name="default_fulfilment_method" class="erp-input w-full">
                    <option value=""><?php echo e(__('—')); ?></option>
                    <?php $__currentLoopData = $fulfilmentMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($method->value); ?>" <?php if(old('default_fulfilment_method', $spec?->default_fulfilment_method?->value) === $method->value): echo 'selected'; endif; ?>>
                            <?php echo e($method->label()); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="erp-label" for="customer_instructions"><?php echo e(__('Customer instructions')); ?></label>
                <textarea id="customer_instructions" name="customer_instructions" class="erp-input w-full" rows="2"><?php echo e(old('customer_instructions', $spec?->customer_instructions)); ?></textarea>
            </div>
        </div>
    </section>

    <section class="rounded-lg border border-erp-border p-4">
        <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Product / Inventory Item')); ?></h3>
        <?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'inventory_item_id','label' => __('Product / Inventory Item'),'options' => collect(old('inventory_item_id', $spec?->inventory_item_id) ? [[
                'value' => old('inventory_item_id', $spec?->inventory_item_id),
                'label' => $item?->item_name ? $item->item_name.' ('.$item->sku.')' : __('Selected product'),
            ]] : []),'value' => old('inventory_item_id', $spec?->inventory_item_id),'required' => true,'createRoute' => 'admin.inventory.items.quick-create','refreshRoute' => 'admin.lookups.items','permission' => 'catalogue.create','modalTitle' => __('Create product'),'selectClass' => 'erp-input w-full','emptyOption' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'inventory_item_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Product / Inventory Item')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(collect(old('inventory_item_id', $spec?->inventory_item_id) ? [[
                'value' => old('inventory_item_id', $spec?->inventory_item_id),
                'label' => $item?->item_name ? $item->item_name.' ('.$item->sku.')' : __('Selected product'),
            ]] : [])),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('inventory_item_id', $spec?->inventory_item_id)),'required' => true,'create-route' => 'admin.inventory.items.quick-create','refresh-route' => 'admin.lookups.items','permission' => 'catalogue.create','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create product')),'select-class' => 'erp-input w-full','empty-option' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
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
        <p class="mt-2 text-xs text-slate-500"><?php echo e(__('Manufacturing defaults (BOM, route, QC, serial capability) come from the catalogue product.')); ?></p>
    </section>

    <?php if($showArtworkUpload): ?>
        <section class="rounded-lg border border-erp-border p-4">
            <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Artwork Versions')); ?></h3>
            <?php if($spec && $spec->artworkVersions->isNotEmpty()): ?>
                <div class="mb-4 overflow-x-auto">
                    <table class="erp-table w-full text-sm">
                        <thead>
                            <tr>
                                <th><?php echo e(__('Version')); ?></th>
                                <th><?php echo e(__('File')); ?></th>
                                <th><?php echo e(__('Uploaded')); ?></th>
                                <th><?php echo e(__('Status')); ?></th>
                                <th><?php echo e(__('Notes')); ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $spec->artworkVersions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $artwork): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <?php echo e($artwork->versionLabel()); ?>

                                        <?php if($artwork->is_active_version): ?><span class="erp-badge"><?php echo e(__('Active')); ?></span><?php endif; ?>
                                    </td>
                                    <td class="max-w-[10rem] truncate"><?php echo e($artwork->originalFileName()); ?></td>
                                    <td><?php echo e($artwork->uploaded_at?->format('Y-m-d')); ?></td>
                                    <td><?php echo e($artwork->status->label()); ?></td>
                                    <td class="max-w-[8rem] truncate"><?php echo e($artwork->change_notes); ?></td>
                                    <td>
                                        <?php if($artwork->isPreviewable() && $artwork->previewUrl()): ?>
                                            <button
                                                type="button"
                                                class="erp-btn-ghost text-xs min-h-[2.25rem] px-2"
                                                data-preview-url="<?php echo e($artwork->previewUrl()); ?>"
                                                data-preview-title="<?php echo e($spec->name); ?>"
                                                data-preview-pdf="<?php echo e($artwork->mime_type === 'application/pdf' ? '1' : '0'); ?>"
                                                @click="show($el.dataset.previewUrl, $el.dataset.previewTitle, $el.dataset.previewPdf === '1')"
                                            ><?php echo e(__('Preview')); ?></button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="mb-3 text-sm text-slate-500"><?php echo e(__('No artwork versions yet.')); ?></p>
            <?php endif; ?>

            <?php if(! $spec): ?>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div>
                        <label class="erp-label" for="artwork_type"><?php echo e(__('Artwork type')); ?></label>
                        <select id="artwork_type" name="artwork_type" class="erp-input w-full">
                            <?php $__currentLoopData = $artworkTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($type->value); ?>"><?php echo e($type->label()); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="erp-label" for="artwork_file"><?php echo e(__('Initial artwork file')); ?></label>
                        <input type="file" id="artwork_file" name="artwork_file" class="erp-input w-full" accept=".jpg,.jpeg,.png,.webp,.pdf">
                    </div>
                    <div class="md:col-span-2">
                        <label class="erp-label" for="artwork_change_notes"><?php echo e(__('Change notes')); ?></label>
                        <input id="artwork_change_notes" name="artwork_change_notes" class="erp-input w-full" maxlength="2000">
                    </div>
                </div>
                <p class="mt-2 text-xs text-slate-500"><?php echo e(__('Versions are never overwritten. Each upload creates a new version.')); ?></p>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <section class="rounded-lg border border-erp-border p-4">
        <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Serial Settings')); ?></h3>
        <?php if($serialSummary && ($serialSummary['uses_serial_numbers'] ?? false)): ?>
            <dl class="mb-4 grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                <div><dt class="text-slate-500"><?php echo e(__('Product default')); ?></dt><dd><code><?php echo e($serialSummary['product_prefix']); ?><?php echo e(str_repeat('0', max(0, ($serialSummary['product_padding'] ?? 6) - 1))); ?>1</code></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Next number')); ?></dt><dd><?php echo e($serialSummary['next_number'] ?? '—'); ?></dd></div>
                <?php if($serialSummary['last_allocation'] ?? null): ?>
                    <div class="sm:col-span-2"><dt class="text-slate-500"><?php echo e(__('Last allocated range')); ?></dt><dd><code><?php echo e($serialSummary['last_allocation']['prefix']); ?><?php echo e($serialSummary['last_allocation']['start']); ?> – <?php echo e($serialSummary['last_allocation']['end']); ?></code></dd></div>
                <?php endif; ?>
            </dl>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <label class="erp-label" for="serial_prefix"><?php echo e(__('Customer prefix override')); ?></label>
                    <input id="serial_prefix" name="serial_prefix" class="erp-input w-full" maxlength="30"
                        value="<?php echo e(old('serial_prefix', $serialProfile?->serial_prefix ?? $serialSummary['customer_prefix'] ?? '')); ?>"
                        placeholder="<?php echo e($serialSummary['product_prefix'] ?? 'FL-'); ?>">
                </div>
                <div>
                    <label class="erp-label" for="serial_padding_length"><?php echo e(__('Padding length')); ?></label>
                    <input type="number" id="serial_padding_length" name="serial_padding_length" class="erp-input w-full" min="1" max="12"
                        value="<?php echo e(old('serial_padding_length', $serialProfile?->serial_padding_length ?? $serialSummary['customer_padding'] ?? 6)); ?>">
                </div>
            </div>
        <?php else: ?>
            <p class="text-sm text-slate-500"><?php echo e(__('Serial numbering applies when the linked product is serial-enabled.')); ?></p>
        <?php endif; ?>
    </section>

    <section class="rounded-lg border border-erp-border p-4">
        <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Production Notes')); ?></h3>
        <textarea name="production_notes" class="erp-input w-full" rows="3"><?php echo e(old('production_notes', $spec?->production_notes)); ?></textarea>
    </section>

    <section class="rounded-lg border border-erp-border p-4">
        <h3 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('Commercial Notes')); ?></h3>
        <textarea name="commercial_notes" class="erp-input w-full" rows="3"><?php echo e(old('commercial_notes', $spec?->commercial_notes)); ?></textarea>
    </section>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\print-specifications\partials\form.blade.php ENDPATH**/ ?>
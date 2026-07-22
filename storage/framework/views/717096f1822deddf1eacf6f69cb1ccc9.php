<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['form', 'canManage']));

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

foreach (array_filter((['form', 'canManage']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if($form['has_status_options'] ?? false): ?>
    <details class="group border-t border-erp-border bg-sky-50/40" open>
        <summary class="cursor-pointer list-none px-4 py-2 text-sm font-medium text-erp-primary hover:bg-sky-50/80 sm:px-5 [&::-webkit-details-marker]:hidden">
            <span class="inline-flex items-center gap-2">
                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'chevron-down','class' => 'h-4 w-4 -rotate-90 text-slate-400 transition-transform group-open:rotate-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-down','class' => 'h-4 w-4 -rotate-90 text-slate-400 transition-transform group-open:rotate-0']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                <?php echo e(__('Status dropdown options')); ?>

            </span>
        </summary>

        <div class="border-t border-erp-border px-4 py-3 sm:px-5">
            <p class="mb-3 text-xs text-slate-600">
                <?php echo e(__('Manage the values shown in this form\'s status dropdown. System statuses cannot be removed, but you can add custom statuses for your company.')); ?>

            </p>

            <div class="overflow-x-auto">
                <table class="erp-table erp-table--grid w-full">
                    <thead>
                        <tr>
                            <th><?php echo e(__('Value')); ?></th>
                            <th><?php echo e(__('Label')); ?></th>
                            <th><?php echo e(__('Active')); ?></th>
                            <?php if($canManage): ?>
                                <th class="text-right"><?php echo e(__('Actions')); ?></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-erp-border bg-white" data-status-options-body>
                        <?php $__currentLoopData = $form['status_options']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="py-2">
                                    <?php if($option['is_system']): ?>
                                        <input type="hidden" name="forms[<?php echo e($form['form_key']); ?>][status_options][<?php echo e($index); ?>][value]" value="<?php echo e($option['value']); ?>">
                                        <span class="font-mono text-sm text-slate-700"><?php echo e($option['value']); ?></span>
                                        <span class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-slate-500"><?php echo e(__('System')); ?></span>
                                    <?php elseif($canManage): ?>
                                        <input
                                            type="text"
                                            name="forms[<?php echo e($form['form_key']); ?>][status_options][<?php echo e($index); ?>][value]"
                                            value="<?php echo e($option['value']); ?>"
                                            class="erp-input w-full min-w-[8rem] font-mono text-sm"
                                            pattern="[a-z0-9_]+"
                                            placeholder="on_hold"
                                        >
                                    <?php else: ?>
                                        <span class="font-mono text-sm text-slate-700"><?php echo e($option['value']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2">
                                    <?php if($canManage): ?>
                                        <input
                                            type="text"
                                            name="forms[<?php echo e($form['form_key']); ?>][status_options][<?php echo e($index); ?>][label]"
                                            value="<?php echo e($option['label']); ?>"
                                            class="erp-input w-full min-w-[10rem]"
                                            required
                                        >
                                    <?php else: ?>
                                        <span class="text-slate-700"><?php echo e($option['label']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2 text-center">
                                    <?php if($canManage): ?>
                                        <input type="hidden" name="forms[<?php echo e($form['form_key']); ?>][status_options][<?php echo e($index); ?>][is_active]" value="0">
                                        <input
                                            type="checkbox"
                                            name="forms[<?php echo e($form['form_key']); ?>][status_options][<?php echo e($index); ?>][is_active]"
                                            value="1"
                                            class="rounded border-erp-border text-erp-accent focus:ring-erp-accent"
                                            <?php if($option['is_active']): echo 'checked'; endif; ?>
                                        >
                                    <?php else: ?>
                                        <span class="text-slate-600"><?php echo e($option['is_active'] ? __('Yes') : __('No')); ?></span>
                                    <?php endif; ?>
                                </td>
                                <?php if($canManage): ?>
                                    <td class="py-2 text-right">
                                        <?php if(! $option['is_system']): ?>
                                            <label class="inline-flex cursor-pointer items-center gap-1 text-xs font-medium text-red-600 hover:text-red-700">
                                                <input
                                                    type="checkbox"
                                                    name="forms[<?php echo e($form['form_key']); ?>][status_options][<?php echo e($index); ?>][remove]"
                                                    value="1"
                                                    class="rounded border-red-300 text-red-600 focus:ring-red-500"
                                                >
                                                <?php echo e(__('Remove')); ?>

                                            </label>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-400"><?php echo e(__('Built-in')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <?php if($canManage): ?>
                <button
                    type="button"
                    class="mt-3 inline-flex items-center gap-1 rounded-md border border-dashed border-erp-border px-3 py-1.5 text-xs font-medium text-erp-accent hover:border-erp-accent hover:bg-white"
                    data-add-status-option
                    data-form-key="<?php echo e($form['form_key']); ?>"
                    data-next-index="<?php echo e(count($form['status_options'])); ?>"
                >
                    <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'plus','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus','class' => 'h-3.5 w-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                    <?php echo e(__('Add status')); ?>

                </button>

                <template id="status-option-row-template-<?php echo e($form['form_key']); ?>">
                    <tr>
                        <td class="py-2">
                            <input
                                type="text"
                                name="forms[<?php echo e($form['form_key']); ?>][status_options][__INDEX__][value]"
                                class="erp-input w-full min-w-[8rem] font-mono text-sm"
                                pattern="[a-z0-9_]+"
                                placeholder="on_hold"
                                required
                            >
                        </td>
                        <td class="py-2">
                            <input
                                type="text"
                                name="forms[<?php echo e($form['form_key']); ?>][status_options][__INDEX__][label]"
                                class="erp-input w-full min-w-[10rem]"
                                placeholder="<?php echo e(__('On hold')); ?>"
                                required
                            >
                        </td>
                        <td class="py-2 text-center">
                            <input type="hidden" name="forms[<?php echo e($form['form_key']); ?>][status_options][__INDEX__][is_active]" value="0">
                            <input
                                type="checkbox"
                                name="forms[<?php echo e($form['form_key']); ?>][status_options][__INDEX__][is_active]"
                                value="1"
                                class="rounded border-erp-border text-erp-accent focus:ring-erp-accent"
                                checked
                            >
                        </td>
                        <td class="py-2 text-right">
                            <button type="button" class="text-xs font-medium text-red-600 hover:text-red-700" data-remove-status-row>
                                <?php echo e(__('Remove')); ?>

                            </button>
                        </td>
                    </tr>
                </template>

                <script>
                    document.getElementById('form-panel-<?php echo e($form['form_key']); ?>')?.querySelector('[data-add-status-option]')?.addEventListener('click', (event) => {
                        const button = event.currentTarget;
                        const formKey = button.dataset.formKey;
                        const template = document.getElementById(`status-option-row-template-${formKey}`);
                        const body = button.closest('details')?.querySelector('[data-status-options-body]');

                        if (! template || ! body) {
                            return;
                        }

                        const index = Number(button.dataset.nextIndex || body.children.length);
                        const row = template.content.cloneNode(true);

                        row.querySelectorAll('[name]').forEach((input) => {
                            input.name = input.name.replace('__INDEX__', String(index));
                        });

                        body.appendChild(row);
                        button.dataset.nextIndex = String(index + 1);
                    });

                    document.getElementById('form-panel-<?php echo e($form['form_key']); ?>')?.addEventListener('click', (event) => {
                        const removeButton = event.target.closest('[data-remove-status-row]');

                        if (! removeButton) {
                            return;
                        }

                        removeButton.closest('tr')?.remove();
                    });
                </script>
            <?php endif; ?>
        </div>
    </details>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\settings\forms\partials\status-options-panel.blade.php ENDPATH**/ ?>
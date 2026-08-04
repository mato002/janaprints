<div class="space-y-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h3 class="text-sm font-semibold text-slate-900"><?php echo e(__('Print Specifications')); ?></h3>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $customer)): ?>
            <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['variant' => 'primary','size' => 'sm','href' => route('admin.crm.customers.print-specifications.create', $customer),'dataTurboFrame' => 'erp-form-modal','class' => 'min-h-[2.75rem]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','size' => 'sm','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.crm.customers.print-specifications.create', $customer)),'data-turbo-frame' => 'erp-form-modal','class' => 'min-h-[2.75rem]']); ?><?php echo e(__('New specification')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $attributes = $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $component = $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
        <?php endif; ?>
    </div>

    <form method="GET" action="<?php echo e(route('admin.crm.customers.show', $customer)); ?>" class="grid grid-cols-1 gap-2 rounded-lg border border-erp-border p-3 sm:grid-cols-2 lg:grid-cols-6">
        <input type="hidden" name="tab" value="print-specifications">
        <div class="lg:col-span-2">
            <label class="erp-label" for="spec_search"><?php echo e(__('Search')); ?></label>
            <input id="spec_search" name="spec_search" class="erp-input w-full" value="<?php echo e(request('spec_search')); ?>" placeholder="<?php echo e(__('Code, name…')); ?>">
        </div>
        <div>
            <label class="erp-label" for="spec_status"><?php echo e(__('Status')); ?></label>
            <select id="spec_status" name="spec_status" class="erp-input w-full">
                <option value=""><?php echo e(__('All')); ?></option>
                <?php $__currentLoopData = $printSpecStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($status->value); ?>" <?php if(request('spec_status') === $status->value): echo 'selected'; endif; ?>><?php echo e($status->label()); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div>
            <label class="erp-label" for="spec_serial_prefix"><?php echo e(__('Serial prefix')); ?></label>
            <input id="spec_serial_prefix" name="spec_serial_prefix" class="erp-input w-full" value="<?php echo e(request('spec_serial_prefix')); ?>">
        </div>
        <div>
            <label class="erp-label" for="spec_artwork_version"><?php echo e(__('Artwork version')); ?></label>
            <input id="spec_artwork_version" name="spec_artwork_version" type="number" min="1" class="erp-input w-full" value="<?php echo e(request('spec_artwork_version')); ?>">
        </div>
        <div class="flex items-end gap-2">
            <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['variant' => 'outline','size' => 'sm','type' => 'submit','class' => 'min-h-[2.5rem]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'outline','size' => 'sm','type' => 'submit','class' => 'min-h-[2.5rem]']); ?><?php echo e(__('Filter')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $attributes = $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $component = $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['variant' => 'ghost','size' => 'sm','href' => route('admin.crm.customers.show', ['customer' => $customer, 'tab' => 'print-specifications']),'class' => 'min-h-[2.5rem]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','size' => 'sm','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.crm.customers.show', ['customer' => $customer, 'tab' => 'print-specifications'])),'class' => 'min-h-[2.5rem]']); ?><?php echo e(__('Reset')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $attributes = $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $component = $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
        </div>
    </form>

    <?php if($printSpecifications->isNotEmpty()): ?>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th><?php echo e(__('Code')); ?></th>
                        <th><?php echo e(__('Name')); ?></th>
                        <th><?php echo e(__('Product')); ?></th>
                        <th><?php echo e(__('Artwork')); ?></th>
                        <th><?php echo e(__('Status')); ?></th>
                        <th class="text-right"><?php echo e(__('Orders')); ?></th>
                        <th class="text-right"><?php echo e(__('Revenue')); ?></th>
                        <th><?php echo e(__('Last ordered')); ?></th>
                        <th class="text-right"><?php echo e(__('Actions')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $printSpecifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $spec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $activeArt = $spec->activeArtworkVersion;
                            $item = $spec->inventoryItem;
                        ?>
                        <tr>
                            <td><code class="text-xs"><?php echo e($spec->specification_code); ?></code></td>
                            <td>
                                <a class="font-medium text-erp-accent hover:underline" href="<?php echo e(route('admin.crm.customers.print-specifications.show', [$customer, $spec])); ?>">
                                    <?php echo e($spec->name); ?>

                                </a>
                            </td>
                            <td><?php echo e($item?->item_name ?? '—'); ?></td>
                            <td><?php echo e($activeArt?->versionLabel() ?? '—'); ?></td>
                            <td><span class="erp-badge"><?php echo e($spec->status->label()); ?></span></td>
                            <td class="text-right tabular-nums"><?php echo e($spec->orders_count ?? 0); ?></td>
                            <td class="text-right tabular-nums"><?php echo e(number_format((float) ($spec->total_revenue ?? 0), 2)); ?></td>
                            <td><?php echo e($spec->last_used_at ? \Illuminate\Support\Carbon::parse($spec->last_used_at)->format('Y-m-d') : '—'); ?></td>
                            <td class="erp-table-actions-col">
                                <?php if (isset($component)) { $__componentOriginalb5a89013017505cf4d4d69115d724d07 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb5a89013017505cf4d4d69115d724d07 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-row-actions','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-row-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                                    <?php if (isset($component)) { $__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-row-action','data' => ['href' => route('admin.crm.customers.print-specifications.show', [$customer, $spec])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.crm.customers.print-specifications.show', [$customer, $spec]))]); ?>
                                        <?php echo e(__('Open')); ?>

                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0)): ?>
<?php $attributes = $__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0; ?>
<?php unset($__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0)): ?>
<?php $component = $__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0; ?>
<?php unset($__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0); ?>
<?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $customer)): ?>
                                        <?php if (! ($spec->isReadOnly())): ?>
                                            <?php if (isset($component)) { $__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-row-action','data' => ['href' => route('admin.crm.customers.print-specifications.edit', [$customer, $spec]),'dataTurboFrame' => 'erp-form-modal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.crm.customers.print-specifications.edit', [$customer, $spec])),'data-turbo-frame' => 'erp-form-modal']); ?>
                                                <?php echo e(__('Edit')); ?>

                                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0)): ?>
<?php $attributes = $__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0; ?>
<?php unset($__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0)): ?>
<?php $component = $__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0; ?>
<?php unset($__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0); ?>
<?php endif; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sales_orders.create')): ?>
                                        <?php if($spec->isSelectableForOrders()): ?>
                                            <?php if (isset($component)) { $__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-row-action','data' => ['href' => route('admin.sales-orders.create', ['customer_id' => $customer->id, 'tab' => 'direct', 'print_specification_id' => $spec->id]),'dataTurboFrame' => 'erp-form-modal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.sales-orders.create', ['customer_id' => $customer->id, 'tab' => 'direct', 'print_specification_id' => $spec->id])),'data-turbo-frame' => 'erp-form-modal']); ?>
                                                <?php echo e(__('Create Order')); ?>

                                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0)): ?>
<?php $attributes = $__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0; ?>
<?php unset($__attributesOriginalba3edbcdd111d8ddf5ca96c9a176d3d0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0)): ?>
<?php $component = $__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0; ?>
<?php unset($__componentOriginalba3edbcdd111d8ddf5ca96c9a176d3d0); ?>
<?php endif; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb5a89013017505cf4d4d69115d724d07)): ?>
<?php $attributes = $__attributesOriginalb5a89013017505cf4d4d69115d724d07; ?>
<?php unset($__attributesOriginalb5a89013017505cf4d4d69115d724d07); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb5a89013017505cf4d4d69115d724d07)): ?>
<?php $component = $__componentOriginalb5a89013017505cf4d4d69115d724d07; ?>
<?php unset($__componentOriginalb5a89013017505cf4d4d69115d724d07); ?>
<?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4"><?php echo e($printSpecifications->withQueryString()->links()); ?></div>
    <?php else: ?>
        <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'document-text','title' => __('No print specifications yet'),'description' => __('Create a specification to link a catalogue product, artwork versions, and serial settings for this customer.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'document-text','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No print specifications yet')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create a specification to link a catalogue product, artwork versions, and serial settings for this customer.'))]); ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $customer)): ?>
                 <?php $__env->slot('action', null, []); ?> 
                    <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['variant' => 'primary','size' => 'sm','href' => route('admin.crm.customers.print-specifications.create', $customer),'dataTurboFrame' => 'erp-form-modal','class' => 'min-h-[2.75rem]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','size' => 'sm','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.crm.customers.print-specifications.create', $customer)),'data-turbo-frame' => 'erp-form-modal','class' => 'min-h-[2.75rem]']); ?><?php echo e(__('New specification')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $attributes = $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $component = $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
                 <?php $__env->endSlot(); ?>
            <?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\360\tab-print-specifications.blade.php ENDPATH**/ ?>
<?php if (isset($component)) { $__componentOriginald3ad0f200dc20b794011e332a16c068d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3ad0f200dc20b794011e332a16c068d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.modal-form','data' => ['title' => __('New job card'),'breadcrumbs' => [
        ['label' => __('Job Cards'), 'url' => route('admin.production.job-cards.index')],
        ['label' => __('New')],
    ],'maxWidth' => 'xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.modal-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('New job card')),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label' => __('Job Cards'), 'url' => route('admin.production.job-cards.index')],
        ['label' => __('New')],
    ]),'maxWidth' => 'xl']); ?>
    <?php
        $resolution = $resolutionContext ?? [
            'summary' => $eligibilitySummary ?? ['already_have_job' => 0, 'blocked_artwork' => 0, 'ready_without_job' => 0],
            'already_have_job' => [],
            'blocked_artwork' => [],
        ];
        $summary = $resolution['summary'];
        $lookupOptions = $eligibleOrders->map(fn ($order) => [
            'value' => $order->id,
            'label' => trim($order->order_number.' — '.($order->customer?->company_name ?? '')),
        ])->values()->all();
    ?>

    <div
        class="space-y-4"
        x-data="erpContinuousWorkspace({ reloadOnReturn: true })"
    >
        <?php if (isset($component)) { $__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-shell','data' => ['action' => route('admin.production.job-cards.store')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.production.job-cards.store'))]); ?>
            <?php if($fromProductionFloor ?? request('from') === 'production-floor'): ?>
                <input type="hidden" name="from" value="production-floor">
            <?php endif; ?>
            <div class="erp-form-grid">
                <div class="md:col-span-2 space-y-3">
                    <?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'sales_order_id','label' => __('Sales order'),'options' => $lookupOptions,'value' => old('sales_order_id', $preselectedSalesOrderId ?? null),'required' => true,'refreshRoute' => 'admin.lookups.job_card_sales_orders','placeholder' => __('Select sales order')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'sales_order_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Sales order')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($lookupOptions),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('sales_order_id', $preselectedSalesOrderId ?? null)),'required' => true,'refresh-route' => 'admin.lookups.job_card_sales_orders','placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Select sales order'))]); ?>
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

                    <?php if($eligibleOrders->isEmpty()): ?>
                        <div class="rounded-md border border-amber-200 bg-amber-50 px-3 py-3 text-sm text-amber-950">
                            <p class="font-medium"><?php echo e(__('Nothing ready to start here — resolve the blocker without leaving this screen.')); ?></p>
                            <p class="mt-1 text-xs text-amber-900/80">
                                <?php echo e(__('When Sales already released an order, open its job card. When artwork is pending, approve it here, then check again.')); ?>

                            </p>

                            <?php if(! empty($resolution['already_have_job'])): ?>
                                <div class="mt-3 space-y-2">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-900/70"><?php echo e(__('Already have a job card')); ?></p>
                                    <ul class="space-y-1.5">
                                        <?php $__currentLoopData = $resolution['already_have_job']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li class="flex flex-wrap items-center justify-between gap-2 rounded border border-amber-200/80 bg-white/70 px-2.5 py-2 text-xs">
                                                <span class="min-w-0 truncate"><?php echo e($row['label']); ?> · <?php echo e($row['job_label']); ?></span>
                                                <?php if($row['job_url']): ?>
                                                    <a href="<?php echo e($row['job_url']); ?>" class="erp-btn-secondary shrink-0 !px-2 !py-1 text-xs" data-erp-modal-open>
                                                        <?php echo e(__('Continue job')); ?>

                                                    </a>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if(! empty($resolution['blocked_artwork'])): ?>
                                <div class="mt-3 space-y-2">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-900/70"><?php echo e(__('Need approved artwork')); ?></p>
                                    <ul class="space-y-1.5">
                                        <?php $__currentLoopData = $resolution['blocked_artwork']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li class="flex flex-wrap items-center justify-between gap-2 rounded border border-amber-200/80 bg-white/70 px-2.5 py-2 text-xs">
                                                <span class="min-w-0 truncate"><?php echo e($row['label']); ?></span>
                                                <?php if($row['resolve_url']): ?>
                                                    <a href="<?php echo e($row['resolve_url']); ?>" class="erp-btn-secondary shrink-0 !px-2 !py-1 text-xs" data-erp-modal-open>
                                                        <?php echo e($row['resolve_label']); ?>

                                                    </a>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if(($summary['already_have_job'] ?? 0) === 0 && ($summary['blocked_artwork'] ?? 0) === 0): ?>
                                <p class="mt-2 text-xs">
                                    <?php echo e(__('No confirmed orders yet. Create a sales order below, then return here to continue.')); ?>

                                </p>
                            <?php endif; ?>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <?php if($salesOrderCreateUrl): ?>
                                    <a href="<?php echo e($salesOrderCreateUrl); ?>" class="erp-btn-secondary text-sm" data-erp-modal-open>
                                        <?php echo e(__('Create sales order')); ?>

                                    </a>
                                <?php endif; ?>
                                <button type="button" class="erp-btn-secondary text-sm" @click="checkAgain()">
                                    <?php echo e(__('Check again')); ?>

                                </button>
                            </div>
                        </div>
                    <?php elseif($salesOrderCreateUrl): ?>
                        <p class="text-xs text-slate-500">
                            <a href="<?php echo e($salesOrderCreateUrl); ?>" class="font-medium text-slate-700 underline-offset-2 hover:underline" data-erp-modal-open>
                                <?php echo e(__('Create another sales order')); ?>

                            </a>
                            <?php echo e(__('without leaving this form — you will return here after saving.')); ?>

                        </p>
                    <?php endif; ?>
                </div>

                <?php if (isset($component)) { $__componentOriginalcaa826401539fc57a784dadbb5b3020d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcaa826401539fc57a784dadbb5b3020d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.select','data' => ['name' => 'production_type','label' => __('Production type'),'required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'production_type','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Production type')),'required' => true]); ?>
                    <?php $__currentLoopData = $productionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($type->value); ?>" <?php if(old('production_type', 'mixed') === $type->value): echo 'selected'; endif; ?>>
                            <?php echo e(ucfirst(str_replace('_', ' ', $type->value))); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcaa826401539fc57a784dadbb5b3020d)): ?>
<?php $attributes = $__attributesOriginalcaa826401539fc57a784dadbb5b3020d; ?>
<?php unset($__attributesOriginalcaa826401539fc57a784dadbb5b3020d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcaa826401539fc57a784dadbb5b3020d)): ?>
<?php $component = $__componentOriginalcaa826401539fc57a784dadbb5b3020d; ?>
<?php unset($__componentOriginalcaa826401539fc57a784dadbb5b3020d); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginalcaa826401539fc57a784dadbb5b3020d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcaa826401539fc57a784dadbb5b3020d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.select','data' => ['name' => 'priority','label' => __('Priority'),'required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'priority','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Priority')),'required' => true]); ?>
                    <?php $__currentLoopData = $priorities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priority): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($priority->value); ?>" <?php if(old('priority', 'normal') === $priority->value): echo 'selected'; endif; ?>>
                            <?php echo e(ucfirst($priority->value)); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcaa826401539fc57a784dadbb5b3020d)): ?>
<?php $attributes = $__attributesOriginalcaa826401539fc57a784dadbb5b3020d; ?>
<?php unset($__attributesOriginalcaa826401539fc57a784dadbb5b3020d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcaa826401539fc57a784dadbb5b3020d)): ?>
<?php $component = $__componentOriginalcaa826401539fc57a784dadbb5b3020d; ?>
<?php unset($__componentOriginalcaa826401539fc57a784dadbb5b3020d); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal187e7f8ae725d0d7c586a97e85953c03 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.input','data' => ['name' => 'planned_start_date','type' => 'date','label' => __('Planned start'),'value' => old('planned_start_date')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'planned_start_date','type' => 'date','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Planned start')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('planned_start_date'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $attributes = $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $component = $__componentOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal187e7f8ae725d0d7c586a97e85953c03 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.input','data' => ['name' => 'planned_end_date','type' => 'date','label' => __('Planned end'),'value' => old('planned_end_date')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'planned_end_date','type' => 'date','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Planned end')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('planned_end_date'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $attributes = $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $component = $__componentOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>
            </div>

            <?php if (isset($component)) { $__componentOriginal661c5ca87570cde504c602ae668d3776 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal661c5ca87570cde504c602ae668d3776 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-actions','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e(__('Create job card')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $attributes = $__attributesOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__attributesOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $component = $__componentOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__componentOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal661c5ca87570cde504c602ae668d3776)): ?>
<?php $attributes = $__attributesOriginal661c5ca87570cde504c602ae668d3776; ?>
<?php unset($__attributesOriginal661c5ca87570cde504c602ae668d3776); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal661c5ca87570cde504c602ae668d3776)): ?>
<?php $component = $__componentOriginal661c5ca87570cde504c602ae668d3776; ?>
<?php unset($__componentOriginal661c5ca87570cde504c602ae668d3776); ?>
<?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d)): ?>
<?php $attributes = $__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d; ?>
<?php unset($__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d)): ?>
<?php $component = $__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d; ?>
<?php unset($__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d); ?>
<?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/create.blade.php ENDPATH**/ ?>
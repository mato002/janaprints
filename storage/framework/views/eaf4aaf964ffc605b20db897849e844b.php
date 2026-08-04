<?php $outsource = $tabData['outsource'] ?? []; ?>

<?php if (isset($component)) { $__componentOriginalf57220fba53d148717b4781691527db9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf57220fba53d148717b4781691527db9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.job-module-card','data' => ['theme' => 'outsourcing','title' => __('Outsourcing'),'icon' => 'truck','compact' => true,'id' => 'outsource']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.job-module-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['theme' => 'outsourcing','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Outsourcing')),'icon' => 'truck','compact' => true,'id' => 'outsource']); ?>
    <?php if($outsource['vendor'] ?? null): ?>
        <div class="mb-3 grid grid-cols-2 gap-2">
            <?php if (isset($component)) { $__componentOriginal3919388ffc59925ca44d4d29cc578eca = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3919388ffc59925ca44d4d29cc578eca = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.job-kpi-tile','data' => ['theme' => 'dispatch','label' => __('Vendor'),'value' => $outsource['vendor']->vendor_name]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.job-kpi-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['theme' => 'dispatch','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Vendor')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($outsource['vendor']->vendor_name)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3919388ffc59925ca44d4d29cc578eca)): ?>
<?php $attributes = $__attributesOriginal3919388ffc59925ca44d4d29cc578eca; ?>
<?php unset($__attributesOriginal3919388ffc59925ca44d4d29cc578eca); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3919388ffc59925ca44d4d29cc578eca)): ?>
<?php $component = $__componentOriginal3919388ffc59925ca44d4d29cc578eca; ?>
<?php unset($__componentOriginal3919388ffc59925ca44d4d29cc578eca); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal3919388ffc59925ca44d4d29cc578eca = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3919388ffc59925ca44d4d29cc578eca = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.job-kpi-tile','data' => ['theme' => 'dispatch','label' => __('Expected return'),'value' => $outsource['expected_return']?->format('Y-m-d') ?? '—']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.job-kpi-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['theme' => 'dispatch','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Expected return')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($outsource['expected_return']?->format('Y-m-d') ?? '—')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3919388ffc59925ca44d4d29cc578eca)): ?>
<?php $attributes = $__attributesOriginal3919388ffc59925ca44d4d29cc578eca; ?>
<?php unset($__attributesOriginal3919388ffc59925ca44d4d29cc578eca); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3919388ffc59925ca44d4d29cc578eca)): ?>
<?php $component = $__componentOriginal3919388ffc59925ca44d4d29cc578eca; ?>
<?php unset($__componentOriginal3919388ffc59925ca44d4d29cc578eca); ?>
<?php endif; ?>
        </div>
        <?php if($outsource['notes']): ?>
            <p class="mb-3 text-sm text-slate-600"><?php echo e($outsource['notes']); ?></p>
        <?php endif; ?>
    <?php endif; ?>

    <?php if($outsource['can_outsource'] ?? false): ?>
        <form method="POST" action="<?php echo e(route('admin.production.job-cards.outsource', $jobCard)); ?>" class="grid grid-cols-1 gap-3 md:grid-cols-2 max-w-2xl">
            <?php echo csrf_field(); ?>
            <div class="md:col-span-2">
                <label class="erp-label"><?php echo e(__('Production vendor')); ?></label>
                <select name="outsource_vendor_id" class="erp-input w-full" required>
                    <?php $__currentLoopData = $outsource['production_vendors'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($vendor->id); ?>"><?php echo e($vendor->vendor_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div><label class="erp-label"><?php echo e(__('Issue date')); ?></label><input type="date" name="outsource_issue_date" class="erp-input w-full" required value="<?php echo e(now()->format('Y-m-d')); ?>"></div>
            <div><label class="erp-label"><?php echo e(__('Expected return')); ?></label><input type="date" name="outsource_expected_return" class="erp-input w-full"></div>
            <div><label class="erp-label"><?php echo e(__('Quoted cost')); ?></label><input type="number" step="0.01" name="outsource_quoted_cost" class="erp-input w-full"></div>
            <div class="md:col-span-2"><label class="erp-label"><?php echo e(__('Notes')); ?></label><textarea name="outsource_notes" class="erp-input w-full" rows="2"></textarea></div>
            <div><button type="submit" class="erp-btn-primary"><?php echo e(__('Outsource production')); ?></button></div>
        </form>
    <?php elseif($outsource['can_return'] ?? false): ?>
        <form method="POST" action="<?php echo e(route('admin.production.job-cards.outsource.return', $jobCard)); ?>" class="flex flex-wrap items-end gap-3 max-w-md">
            <?php echo csrf_field(); ?>
            <div class="flex-1"><label class="erp-label"><?php echo e(__('Actual cost')); ?></label><input type="number" step="0.01" name="outsource_actual_cost" class="erp-input w-full"></div>
            <button type="submit" class="erp-btn-primary"><?php echo e(__('Mark returned')); ?></button>
        </form>
    <?php elseif(! ($outsource['vendor'] ?? null)): ?>
        <p class="text-sm text-slate-500"><?php echo e(__('This job has not been outsourced.')); ?></p>
    <?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf57220fba53d148717b4781691527db9)): ?>
<?php $attributes = $__attributesOriginalf57220fba53d148717b4781691527db9; ?>
<?php unset($__attributesOriginalf57220fba53d148717b4781691527db9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf57220fba53d148717b4781691527db9)): ?>
<?php $component = $__componentOriginalf57220fba53d148717b4781691527db9; ?>
<?php unset($__componentOriginalf57220fba53d148717b4781691527db9); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\partials\outsource.blade.php ENDPATH**/ ?>
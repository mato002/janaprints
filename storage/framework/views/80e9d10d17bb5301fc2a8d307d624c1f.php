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
    <h3 class="mb-4 text-sm font-semibold text-erp-primary"><?php echo e(__('Payroll scope certification')); ?></h3>
    <p class="mb-4 text-sm text-slate-600"><?php echo e(__('Read-only certification of who is included in this payroll run and why others are excluded.')); ?></p>

    <dl class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500"><?php echo e(__('Payroll group')); ?></dt>
            <dd class="mt-1 text-sm font-medium text-slate-900"><?php echo e($scope['certification']['payroll_group_label'] ?? '—'); ?></dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500"><?php echo e(__('Included')); ?></dt>
            <dd class="mt-1 text-sm font-medium text-slate-900"><?php echo e($scope['certification']['included_count'] ?? 0); ?></dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500"><?php echo e(__('Excluded')); ?></dt>
            <dd class="mt-1 text-sm font-medium text-slate-900"><?php echo e($scope['certification']['excluded_count'] ?? 0); ?></dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500"><?php echo e(__('Frozen integrity')); ?></dt>
            <dd class="mt-1 text-sm font-medium <?php echo e(($scope['frozen_intact'] ?? true) ? 'text-emerald-700' : 'text-amber-700'); ?>">
                <?php if($run->frozen_snapshot): ?>
                    <?php echo e(($scope['frozen_intact'] ?? true) ? __('Intact') : __('Changed since approval freeze')); ?>

                <?php else: ?>
                    <?php echo e(__('Not frozen yet')); ?>

                <?php endif; ?>
            </dd>
        </div>
    </dl>

    <?php if(($scope['integrity']['warnings'] ?? []) !== []): ?>
        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <p class="font-medium"><?php echo e(__('Setup warnings before generation')); ?></p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                <?php $__currentLoopData = $scope['integrity']['warnings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($warning['message'] ?? ''); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="grid gap-6 lg:grid-cols-2">
        <div>
            <h4 class="mb-3 text-sm font-semibold text-slate-800"><?php echo e(__('Employees included')); ?></h4>
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="erp-table erp-table--compact min-w-full text-sm">
                    <thead>
                        <tr>
                            <th><?php echo e(__('Employee')); ?></th>
                            <th><?php echo e(__('Group')); ?></th>
                            <th class="text-right"><?php echo e(__('Basic')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $scope['certification']['included'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($row['employee_number']); ?> — <?php echo e($row['employee_name']); ?></td>
                                <td><?php echo e($row['payroll_group_label'] ?? '—'); ?></td>
                                <td class="text-right tabular-nums"><?php echo e($row['basic_salary'] !== null ? number_format($row['basic_salary'], 2) : '—'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="3" class="text-slate-500"><?php echo e(__('No eligible employees for this payroll group.')); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <h4 class="mb-3 text-sm font-semibold text-slate-800"><?php echo e(__('Employees excluded')); ?></h4>
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="erp-table erp-table--compact min-w-full text-sm">
                    <thead>
                        <tr>
                            <th><?php echo e(__('Employee')); ?></th>
                            <th><?php echo e(__('Reason')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $scope['certification']['excluded'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($row['employee_number']); ?> — <?php echo e($row['employee_name']); ?></td>
                                <td><?php echo e($row['exclusion_label'] ?? '—'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="2" class="text-slate-500"><?php echo e(__('No excluded employees in this branch scope.')); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\payroll\360\tabs\scope.blade.php ENDPATH**/ ?>
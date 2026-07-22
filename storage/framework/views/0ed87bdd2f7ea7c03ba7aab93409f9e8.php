<div class="erp-table-scroll">
    <table class="erp-table erp-table--grid min-w-full text-sm">
        <thead>
            <tr>
                <th scope="col"><?php echo e(__('Setting')); ?></th>
                <?php if($editable): ?>
                    <?php if($rows->contains(fn ($row) => in_array('company', $row['scopes'], true))): ?>
                        <th scope="col" class="py-3 px-4"><?php echo e(__('Company override')); ?></th>
                    <?php endif; ?>
                    <?php if($branchId && $rows->contains(fn ($row) => in_array('branch', $row['scopes'], true))): ?>
                        <th scope="col" class="py-3 px-4"><?php echo e(__('Branch override')); ?></th>
                    <?php endif; ?>
                <?php else: ?>
                    <th scope="col" class="py-3 px-4"><?php echo e(__('Company value')); ?></th>
                    <?php if($branchId): ?>
                        <th scope="col" class="py-3 px-4"><?php echo e(__('Branch value')); ?></th>
                    <?php endif; ?>
                <?php endif; ?>
                <th scope="col"><?php echo e(__('Effective')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="py-4 pr-4 align-top">
                        <p class="font-medium text-erp-primary"><?php echo e($row['label']); ?></p>
                        <p class="mt-1 text-xs text-slate-500"><?php echo e($row['description']); ?></p>
                        <p class="mt-1 font-mono text-[11px] text-slate-400"><?php echo e($row['key']); ?></p>
                    </td>

                    <?php if($editable): ?>
                        <?php if($rows->contains(fn ($r) => in_array('company', $r['scopes'], true))): ?>
                            <td class="py-4 px-4 align-top">
                                <?php if(in_array('company', $row['scopes'], true)): ?>
                                    <?php echo $__env->make('admin.settings.partials.setting-input', [
                                        'name' => "settings[{$row['key']}][company]",
                                        'type' => $row['type'],
                                        'value' => $row['company_value'],
                                        'placeholder' => __('Inherit default'),
                                        'allowInherit' => true,
                                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                <?php else: ?>
                                    <span class="text-slate-400">—</span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>

                        <?php if($branchId && $rows->contains(fn ($r) => in_array('branch', $r['scopes'], true))): ?>
                            <td class="py-4 px-4 align-top">
                                <?php if(in_array('branch', $row['scopes'], true)): ?>
                                    <?php echo $__env->make('admin.settings.partials.setting-input', [
                                        'name' => "settings[{$row['key']}][branch]",
                                        'type' => $row['type'],
                                        'value' => $row['branch_value'],
                                        'placeholder' => __('Inherit company'),
                                        'allowInherit' => true,
                                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                <?php else: ?>
                                    <span class="text-slate-400">—</span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    <?php else: ?>
                        <td class="py-4 px-4 align-top tabular-nums text-slate-700">
                            <?php echo $__env->make('admin.settings.partials.setting-display', ['value' => $row['company_value'], 'type' => $row['type'], 'empty' => __('Default')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </td>
                        <?php if($branchId): ?>
                            <td class="py-4 px-4 align-top tabular-nums text-slate-700">
                                <?php echo $__env->make('admin.settings.partials.setting-display', ['value' => $row['branch_value'], 'type' => $row['type'], 'empty' => __('Inherit')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </td>
                        <?php endif; ?>
                    <?php endif; ?>

                    <td class="py-4 pl-4 align-top">
                        <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-800">
                            <?php echo $__env->make('admin.settings.partials.setting-display', ['value' => $row['effective_value'], 'type' => $row['type'], 'empty' => '—'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\settings\partials\settings-table.blade.php ENDPATH**/ ?>
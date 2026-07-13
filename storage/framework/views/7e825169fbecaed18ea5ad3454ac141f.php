<div class="erp-card erp-table-scroll">
    <table class="erp-table erp-table--grid w-full text-sm">
        <thead>
            <tr>
                <th><?php echo e(__('Job')); ?></th>
                <th><?php echo e(__('Customer')); ?></th>
                <th><?php echo e(__('Product')); ?></th>
                <th><?php echo e(__('Stage')); ?></th>
                <th><?php echo e(__('Machine')); ?></th>
                <th><?php echo e(__('Vendor')); ?></th>
                <th><?php echo e(__('Due')); ?></th>
                <th><?php echo e(__('Priority')); ?></th>
                <th class="erp-table-actions-col"><?php echo e(__('Next step')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr
                    class="cursor-pointer hover:bg-slate-50 <?php echo e($row['is_overdue'] ? 'bg-amber-50/60' : ''); ?>"
                    @click="openPanel(<?php echo \Illuminate\Support\Js::from($row['public_id'])->toHtml() ?>)"
                >
                    <td class="font-mono text-xs whitespace-nowrap">
                        <button type="button" class="text-erp-accent hover:underline" @click.stop="openPanel(<?php echo \Illuminate\Support\Js::from($row['public_id'])->toHtml() ?>)">
                            <?php echo e($row['job_number']); ?>

                        </button>
                    </td>
                    <td><?php echo e($row['customer'] ?? '—'); ?></td>
                    <td>
                        <span><?php echo e($row['product'] ?? '—'); ?></span>
                        <?php if($row['sku']): ?>
                            <span class="block text-[11px] text-slate-500"><?php echo e($row['sku']); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide
                            <?php if($row['stage'] === 'at_vendor'): ?> bg-violet-100 text-violet-800
                            <?php elseif($row['is_overdue']): ?> bg-amber-100 text-amber-900
                            <?php else: ?> bg-slate-100 text-slate-700 <?php endif; ?>">
                            <?php echo e($row['stage_label']); ?>

                        </span>
                    </td>
                    <td @click.stop>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('machines.assign')): ?>
                            <form method="POST" action="<?php echo e(route('admin.production.floor.assign-machine', $row['public_id'])); ?>" class="min-w-[9rem]">
                                <?php echo csrf_field(); ?>
                                <?php $__currentLoopData = $filters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($value !== '' && $value !== null): ?>
                                        <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>">
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <select
                                    name="assigned_machine_asset_id"
                                    class="erp-select w-full text-xs"
                                    onchange="this.form.submit()"
                                >
                                    <option value=""><?php echo e(__('Unassigned')); ?></option>
                                    <?php $__currentLoopData = $filter_options['machines']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $machine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($machine['value']); ?>" <?php if((string) $row['machine_id'] === $machine['value']): echo 'selected'; endif; ?>>
                                            <?php echo e($machine['label']); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </form>
                        <?php else: ?>
                            <?php echo e($row['machine'] ?? '—'); ?>

                        <?php endif; ?>
                    </td>
                    <td class="text-xs">
                        <?php if($row['vendor']): ?>
                            <span class="font-medium"><?php echo e($row['vendor']); ?></span>
                            <?php if($row['vendor_expected_return']): ?>
                                <span class="block text-slate-500"><?php echo e(__('Return')); ?>: <?php echo e($row['vendor_expected_return']); ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td class="whitespace-nowrap text-xs <?php echo e($row['is_overdue'] ? 'font-semibold text-amber-800' : ''); ?>">
                        <?php echo e($row['required_date'] ?? '—'); ?>

                    </td>
                    <td class="text-xs capitalize"><?php echo e($row['priority_label']); ?></td>
                    <td class="erp-table-actions-col" @click.stop>
                        <?php if($row['primary_action']): ?>
                            <?php $action = $row['primary_action']; ?>
                            <?php if($action['type'] === 'post'): ?>
                                <form method="POST" action="<?php echo e($action['url']); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="erp-btn-primary text-xs py-1 px-2"><?php echo e($action['label']); ?></button>
                                </form>
                            <?php elseif($action['type'] === 'panel'): ?>
                                <?php
                                    $panelFragment = parse_url($action['url'], PHP_URL_FRAGMENT) ?: '';
                                ?>
                                <button type="button" class="erp-btn-primary text-xs py-1 px-2" @click="openPanel(<?php echo \Illuminate\Support\Js::from($row['public_id'])->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($panelFragment)->toHtml() ?>)">
                                    <?php echo e($action['label']); ?>

                                </button>
                            <?php else: ?>
                                <a href="<?php echo e($action['url']); ?>" class="erp-btn-secondary text-xs py-1 px-2" data-turbo-frame="erp-main"><?php echo e($action['label']); ?></a>
                            <?php endif; ?>
                        <?php else: ?>
                            <button type="button" class="erp-btn-secondary text-xs py-1 px-2" @click="openPanel(<?php echo \Illuminate\Support\Js::from($row['public_id'])->toHtml() ?>)"><?php echo e(__('Open')); ?></button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="9" class="py-10 text-center text-slate-500">
                        <?php echo e(__('No jobs match the current filters.')); ?>

                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\janaprints\resources\views/admin/production/floor/partials/table.blade.php ENDPATH**/ ?>
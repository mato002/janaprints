<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $frame = WorkspaceEmbed::turboFrame();
?>

<section class="rounded-xl border border-erp-border bg-white shadow-sm" aria-label="<?php echo e(__('Awaiting delivery')); ?>">
    <div class="flex items-center justify-between gap-2 border-b border-erp-border px-3 py-2">
        <h2 class="text-sm font-semibold text-slate-900"><?php echo e(__('Awaiting delivery')); ?></h2>
        <a
            href="<?php echo e(WorkspaceEmbed::url(route('admin.procurement.orders.index'))); ?>"
            class="text-[11px] font-semibold text-erp-accent hover:underline"
            data-turbo-frame="<?php echo e($frame); ?>"
            data-turbo-action="advance"
        ><?php echo e(__('Orders')); ?></a>
    </div>

    <?php if(count($receivingPipeline ?? []) === 0): ?>
        <div class="px-3 py-5 text-center text-sm text-slate-500"><?php echo e(__('No open purchase deliveries.')); ?></div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">
                        <th class="px-3 py-2"><?php echo e(__('Supplier')); ?></th>
                        <th class="px-3 py-2"><?php echo e(__('Expected')); ?></th>
                        <th class="px-3 py-2 text-right"><?php echo e(__('Status')); ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__currentLoopData = $receivingPipeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-3 py-2">
                                <a
                                    href="<?php echo e(WorkspaceEmbed::url($row['url'])); ?>"
                                    class="block"
                                    data-turbo-frame="<?php echo e($frame); ?>"
                                    data-turbo-action="advance"
                                >
                                    <span class="block truncate font-medium text-slate-900"><?php echo e($row['supplier']); ?></span>
                                    <span class="block font-mono text-[11px] text-slate-500"><?php echo e($row['label']); ?></span>
                                </a>
                            </td>
                            <td class="px-3 py-2 text-xs text-slate-600"><?php echo e($row['timing']); ?></td>
                            <td class="px-3 py-2 text-right">
                                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'text-xs font-semibold',
                                    'text-rose-700' => $row['overdue'] ?? false,
                                    'text-amber-700' => ! ($row['overdue'] ?? false) && ($row['status'] ?? '') === __('Due today'),
                                    'text-emerald-700' => ! ($row['overdue'] ?? false) && ($row['status'] ?? '') === __('On time'),
                                    'text-slate-600' => ! ($row['overdue'] ?? false) && ! in_array($row['status'] ?? '', [__('Due today'), __('On time')], true),
                                ]); ?>"><?php echo e($row['status'] ?? $row['timing']); ?></span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/procurement/desk/partials/receiving-pipeline.blade.php ENDPATH**/ ?>
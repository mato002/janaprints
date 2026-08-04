<div class="rounded-lg border border-erp-border bg-white p-4" style="<?php echo \Illuminate\Support\Arr::toCssStyles(['margin-left: '.($depth * 1.5).'rem']) ?>">
    <div>
        <div class="font-semibold text-erp-primary"><?php echo e($node['title']); ?></div>
        <div class="mt-1 font-mono text-[11px] text-slate-500"><?php echo e($node['code']); ?></div>
        <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-500">
            <span><?php echo e($node['level']); ?></span>
            <?php if($node['department']): ?>
                <span>· <?php echo e($node['department']); ?></span>
            <?php endif; ?>
            <span>· <?php echo e(trans_choice(':count employee|:count employees', $node['employee_count'], ['count' => $node['employee_count']])); ?></span>
            <?php if($node['approval_authority']): ?>
                <span>· <?php echo e(__('Approval')); ?>: <?php echo e($node['approval_authority']); ?></span>
            <?php endif; ?>
        </div>
    </div>

    <?php if(! empty($node['children'])): ?>
        <div class="mt-3 space-y-3 border-l-2 border-erp-accent/20 pl-4">
            <?php $__currentLoopData = $node['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo $__env->make('admin.job-titles.partials.hierarchy-node', ['node' => $child, 'depth' => $depth + 1], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\job-titles\partials\hierarchy-node.blade.php ENDPATH**/ ?>
<ul class="<?php echo \Illuminate\Support\Arr::toCssClasses(['space-y-1', 'ml-4 border-l border-erp-border pl-3' => $depth > 0]); ?>">
    <?php $__currentLoopData = $nodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $node): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li class="text-[11px] text-slate-600">
            <span class="font-mono text-slate-400"><?php echo e($node['group']->code); ?></span>
            <?php echo e($node['group']->name); ?>

            <?php if($node['children'] !== []): ?>
                <?php echo $__env->make('admin.accounting.accounts.partials.group-tree', ['nodes' => $node['children'], 'depth' => $depth + 1], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>
        </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\accounting\accounts\partials\group-tree.blade.php ENDPATH**/ ?>
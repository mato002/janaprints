<div <?php echo e($attributes->merge(['class' => 'flex flex-col gap-3 border-b border-erp-border px-4 py-3 sm:flex-row sm:items-center sm:justify-between'])); ?>>
    <div class="flex flex-1 flex-wrap items-center gap-2">
        <?php echo e($slot); ?>

    </div>
    <?php if(isset($actions)): ?>
        <div class="flex shrink-0 items-center gap-2">
            <?php echo e($actions); ?>

        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\filter-bar.blade.php ENDPATH**/ ?>
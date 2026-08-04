<?php echo $__env->make('admin.assets.partials.form', [
    'asset' => $asset,
    'action' => route('admin.assets.update', $asset),
    'method' => 'PUT',
    'title' => __('Edit asset'),
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\assets\edit.blade.php ENDPATH**/ ?>
<?php echo $__env->make('admin.assets.categories.partials.form', [
    'category' => $category,
    'action' => route('admin.assets.categories.update', $category),
    'method' => 'PUT',
    'title' => __('Edit asset category'),
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\assets\categories\edit.blade.php ENDPATH**/ ?>
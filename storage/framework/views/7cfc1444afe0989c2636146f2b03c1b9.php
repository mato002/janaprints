<?php
    $fields = $formFields ?? [];

    $registryFields = collect($fields)
        ->filter(fn (array $field) => ! ($field['is_custom'] ?? false) && ($field['visible'] ?? true))
        ->sortBy('sort_order');
?>

<div class="erp-form-grid">
    <?php $__currentLoopData = $registryFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fieldKey => $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php echo $__env->make('admin.crm.leads.partials.registry-field', [
            'fieldKey' => $fieldKey,
            'field' => $field,
            'lead' => $lead ?? null,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php echo $__env->make('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => $lead ?? null, 'formKey' => 'lead'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/crm/leads/form.blade.php ENDPATH**/ ?>
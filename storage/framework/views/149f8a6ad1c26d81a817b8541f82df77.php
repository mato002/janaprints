<?php
    $checks = $readinessChecks ?? [];
    $staffWarning = collect($checks)->firstWhere('key', 'staff_role');
    $defaultRoleWarning = collect($checks)->firstWhere('key', 'default_role');
?>

<?php if(($staffWarning['status'] ?? null) === 'warning'): ?>
    <p class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
        <?php echo e(__('Staff role is not seeded. New activations will fall back to Viewer until Staff is created.')); ?>

    </p>
<?php endif; ?>

<?php if(($defaultRoleWarning['status'] ?? null) === 'warning'): ?>
    <p class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
        <?php echo e(__('No default activation role is available. Activations may complete without an ERP role assignment.')); ?>

    </p>
<?php endif; ?>

<?php if(filled($readinessChecks ?? null)): ?>
    <div class="mt-4">
        <a href="<?php echo e(route('admin.email-identity.index')); ?>" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
            <?php echo e(__('View full email identity readiness checklist')); ?> →
        </a>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\employees\partials\email-readiness-panel.blade.php ENDPATH**/ ?>
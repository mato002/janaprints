<turbo-frame id="module-workspace-content">
    <div class="module-workspace-embedded w-full min-w-0">
        <?php echo $__env->make('admin.partials.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('admin.settings.partials.scope-selector', [
            'action' => route('admin.settings.forms.index', $scopeQuery),
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $companies,
            'branches' => $branches,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('admin.settings.forms.partials.landing', [
            'controlCenter' => $controlCenter,
            'canManage' => $canManage,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</turbo-frame>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/settings/forms/embedded-landing.blade.php ENDPATH**/ ?>
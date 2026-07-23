<?php if (isset($component)) { $__componentOriginal2309e95c2acd56570e09552e56b633ea = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2309e95c2acd56570e09552e56b633ea = $attributes; } ?>
<?php $component = App\View\Components\EssLayout::resolve(['title' => collect($tabs)->firstWhere('id', $activeTab)['label'] ?? __('Overview'),'activeTab' => $activeTab,'tabs' => $tabs] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ess-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\EssLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php echo $__env->renderWhen($activeTab === 'overview', 'ess.tabs.overview', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1])); ?>
    <?php echo $__env->renderWhen($activeTab === 'profile', 'ess.tabs.profile', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1])); ?>
    <?php echo $__env->renderWhen($activeTab === 'payslips', 'ess.tabs.payslips', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1])); ?>
    <?php echo $__env->renderWhen($activeTab === 'payroll-history', 'ess.tabs.payroll-history', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1])); ?>
    <?php echo $__env->renderWhen($activeTab === 'documents', 'ess.tabs.documents', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1])); ?>
    <?php echo $__env->renderWhen($activeTab === 'security', 'ess.tabs.security', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1])); ?>
    <?php echo $__env->renderWhen($activeTab === 'communications', 'ess.tabs.communications', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1])); ?>
    <?php echo $__env->renderWhen($activeTab === 'onboarding', 'ess.tabs.onboarding', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1])); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2309e95c2acd56570e09552e56b633ea)): ?>
<?php $attributes = $__attributesOriginal2309e95c2acd56570e09552e56b633ea; ?>
<?php unset($__attributesOriginal2309e95c2acd56570e09552e56b633ea); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2309e95c2acd56570e09552e56b633ea)): ?>
<?php $component = $__componentOriginal2309e95c2acd56570e09552e56b633ea; ?>
<?php unset($__componentOriginal2309e95c2acd56570e09552e56b633ea); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\ess\workspace.blade.php ENDPATH**/ ?>
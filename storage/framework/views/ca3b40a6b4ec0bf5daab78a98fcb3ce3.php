<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $linkTurboAttrs = WorkspaceEmbed::leaveWorkspaceLinkAttributes();

    $historyLinks = [
        ['route' => 'timeline', 'icon' => 'clock', 'label' => __('Timeline'), 'theme' => 'history'],
        ['route' => 'communications', 'icon' => 'document-text', 'label' => __('Communications'), 'theme' => 'materials'],
        ['route' => 'artwork', 'icon' => 'photograph', 'label' => __('Attachments'), 'theme' => 'qc'],
        ['route' => 'traceability', 'icon' => 'search', 'label' => __('Traceability'), 'theme' => 'dispatch'],
    ];
?>

<?php if (isset($component)) { $__componentOriginalf57220fba53d148717b4781691527db9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf57220fba53d148717b4781691527db9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.job-module-card','data' => ['theme' => 'history','title' => __('History & records'),'icon' => 'clock','compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.job-module-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['theme' => 'history','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('History & records')),'icon' => 'clock','compact' => true]); ?>
    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
        <?php $__currentLoopData = $historyLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a
                href="<?php echo e(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => $link['route']])); ?>"
                class="job-360-history-tile job-360-history-tile--<?php echo e($link['theme']); ?>"
                <?php $__currentLoopData = $linkTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            >
                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $link['icon'],'class' => 'job-360-history-tile__icon h-5 w-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($link['icon']),'class' => 'job-360-history-tile__icon h-5 w-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                <span class="job-360-history-tile__label"><?php echo e($link['label']); ?></span>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf57220fba53d148717b4781691527db9)): ?>
<?php $attributes = $__attributesOriginalf57220fba53d148717b4781691527db9; ?>
<?php unset($__attributesOriginalf57220fba53d148717b4781691527db9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf57220fba53d148717b4781691527db9)): ?>
<?php $component = $__componentOriginalf57220fba53d148717b4781691527db9; ?>
<?php unset($__componentOriginalf57220fba53d148717b4781691527db9); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/workspace/partials/history-zone.blade.php ENDPATH**/ ?>
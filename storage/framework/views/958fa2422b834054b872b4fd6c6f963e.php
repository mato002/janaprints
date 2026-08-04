<?php
    use App\Support\Navigation\WorkspaceEmbed;
    $turboFrame = WorkspaceEmbed::turboFrame();
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Performance'),'breadcrumbs' => [['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Performance')]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginal0d730f64a6ff6dfae141e1800c1126d8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0d730f64a6ff6dfae141e1800c1126d8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.workspace-content-header','data' => ['title' => __('Performance Management'),'description' => __('Employee KPIs, appraisals, and performance ratings.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.workspace-content-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Performance Management')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Employee KPIs, appraisals, and performance ratings.'))]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Hr\PerformanceReview::class)): ?>
                <a href="<?php echo e(route('admin.hr.performance.create')); ?>" class="erp-btn-primary" data-erp-modal-open><?php echo e(__('New appraisal')); ?></a>
            <?php endif; ?>
            <a href="<?php echo e(WorkspaceEmbed::url(route('admin.hr.performance.index'))); ?>" class="erp-btn-secondary erp-btn--sm" data-turbo-frame="<?php echo e($turboFrame); ?>"><?php echo e(__('All reviews')); ?></a>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0d730f64a6ff6dfae141e1800c1126d8)): ?>
<?php $attributes = $__attributesOriginal0d730f64a6ff6dfae141e1800c1126d8; ?>
<?php unset($__attributesOriginal0d730f64a6ff6dfae141e1800c1126d8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0d730f64a6ff6dfae141e1800c1126d8)): ?>
<?php $component = $__componentOriginal0d730f64a6ff6dfae141e1800c1126d8; ?>
<?php unset($__componentOriginal0d730f64a6ff6dfae141e1800c1126d8); ?>
<?php endif; ?>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <?php $__currentLoopData = [
            ['label' => __('Reviews This Year'), 'value' => $stats['reviews_this_year'], 'icon' => 'badge-check'],
            ['label' => __('Submitted'), 'value' => $stats['submitted'], 'icon' => 'check-circle'],
            ['label' => __('Excellent Ratings'), 'value' => $stats['excellent_count'], 'icon' => 'star'],
            ['label' => __('Average Score'), 'value' => $stats['average_score'], 'icon' => 'chart-pie'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => $card['label'],'value' => $card['value'],'icon' => $card['icon']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card['label']),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card['value']),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card['icon'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $attributes = $__attributesOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $component = $__componentOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__componentOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mt-6','title' => __('Recent Appraisals')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Recent Appraisals'))]); ?>
        <?php if($recentReviews->isEmpty()): ?>
            <p class="text-sm text-slate-500"><?php echo e(__('No performance reviews yet.')); ?></p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-slate-500">
                            <th class="py-2 pr-3"><?php echo e(__('Reference')); ?></th>
                            <th class="py-2 pr-3"><?php echo e(__('Employee')); ?></th>
                            <th class="py-2 pr-3"><?php echo e(__('Cycle')); ?></th>
                            <th class="py-2 pr-3"><?php echo e(__('Score')); ?></th>
                            <th class="py-2 pr-3"><?php echo e(__('Rating')); ?></th>
                            <th class="py-2"><?php echo e(__('Status')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $recentReviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="border-b border-slate-100">
                                <td class="py-2 pr-3">
                                    <a href="<?php echo e(route('admin.hr.performance.show', $review)); ?>" class="text-indigo-600 hover:underline"><?php echo e($review->reference); ?></a>
                                </td>
                                <td class="py-2 pr-3"><?php echo e($review->employee->full_name); ?></td>
                                <td class="py-2 pr-3"><?php echo e($review->cycle->label()); ?></td>
                                <td class="py-2 pr-3"><?php echo e(number_format($review->composite_score, 1)); ?></td>
                                <td class="py-2 pr-3"><?php echo e($review->rating?->label() ?? '—'); ?></td>
                                <td class="py-2"><?php echo e($review->status->label()); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $attributes = $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $component = $__componentOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\performance\dashboard.blade.php ENDPATH**/ ?>
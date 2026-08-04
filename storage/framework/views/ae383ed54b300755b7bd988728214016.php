<?php
    $checklist = $checklist ?? app(\App\Support\Website\WebsiteContentWorkspacePresenter::class)->deploymentChecklist();
?>

<div
    x-data="{ guideOpen: false, checklistOpen: false }"
    class="mb-4 flex flex-wrap items-center gap-2"
>
    <button type="button" class="erp-btn-secondary text-xs" @click="guideOpen = !guideOpen">
        <?php echo e(__('Website CMS Guide')); ?>

    </button>
    <button type="button" class="erp-btn-secondary text-xs" @click="checklistOpen = !checklistOpen">
        <?php echo e(__('Deployment Checklist')); ?>

    </button>

    <div
        x-show="guideOpen"
        x-cloak
        class="w-full rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700"
    >
        <h3 class="mb-2 font-semibold text-slate-900"><?php echo e(__('Quick start for non-technical staff')); ?></h3>
        <ol class="list-decimal space-y-2 pl-5">
            <li><?php echo e(__('Replace a homepage image: Media Library → filter Hero → Upload/Replace → save alt text.')); ?></li>
            <li><?php echo e(__('Update footer contact: Footer & Contact Settings → Contact tab → save phone, email, and address.')); ?></li>
            <li><?php echo e(__('Add a gallery project: Gallery → Add Gallery Item → upload image, title, and category.')); ?></li>
            <li><?php echo e(__('Publish gallery work: check Published on public site (requires publish permission).')); ?></li>
            <li><?php echo e(__('Restore a fallback image: Media Library → Reset on the slot, or Remove Uploaded Image on the edit form.')); ?></li>
        </ol>
    </div>

    <div
        x-show="checklistOpen"
        x-cloak
        class="w-full rounded-lg border border-slate-200 bg-white p-4 text-sm"
    >
        <h3 class="mb-3 font-semibold text-slate-900"><?php echo e(__('Deployment readiness (read-only)')); ?></h3>
        <ul class="space-y-2">
            <?php $__currentLoopData = $checklist; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $checklistItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="flex items-start gap-2">
                    <?php if($checklistItem['ready']): ?>
                        <?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'success']); ?><?php echo e(__('Ready')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $attributes = $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $component = $__componentOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
                    <?php else: ?>
                        <?php if (isset($component)) { $__componentOriginal72ffe10338c4ec71bdf1582010227fb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.status-badge','data' => ['variant' => 'neutral']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'neutral']); ?><?php echo e(__('Pending')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $attributes = $__attributesOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__attributesOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9)): ?>
<?php $component = $__componentOriginal72ffe10338c4ec71bdf1582010227fb9; ?>
<?php unset($__componentOriginal72ffe10338c4ec71bdf1582010227fb9); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <div>
                        <p class="font-medium text-slate-900"><?php echo e($checklistItem['label']); ?></p>
                        <p class="text-xs text-slate-500"><?php echo e($checklistItem['detail']); ?></p>
                    </div>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\website\partials\cms-support-panel.blade.php ENDPATH**/ ?>
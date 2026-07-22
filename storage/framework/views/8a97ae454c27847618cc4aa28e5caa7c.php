<?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $header['name'],'description' => $header['code']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($header['name']),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($header['code'])]); ?>
    <div class="flex flex-wrap items-center gap-2">
        <?php if (isset($component)) { $__componentOriginal6b1e38fda99422b6943f33aba545ca7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.enum-status-badge','data' => ['status' => $header['status']->value]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.enum-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($header['status']->value)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6b1e38fda99422b6943f33aba545ca7b)): ?>
<?php $attributes = $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b; ?>
<?php unset($__attributesOriginal6b1e38fda99422b6943f33aba545ca7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6b1e38fda99422b6943f33aba545ca7b)): ?>
<?php $component = $__componentOriginal6b1e38fda99422b6943f33aba545ca7b; ?>
<?php unset($__componentOriginal6b1e38fda99422b6943f33aba545ca7b); ?>
<?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $customer)): ?>
            <?php if (isset($component)) { $__componentOriginal07cb24c7759400d4bcd39cc892e46f4c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-modal-link','data' => ['href' => route('admin.crm.customers.edit', $customer),'variant' => 'secondary','class' => 'text-sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-modal-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.crm.customers.edit', $customer)),'variant' => 'secondary','class' => 'text-sm']); ?><?php echo e(__('Edit customer')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c)): ?>
<?php $attributes = $__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c; ?>
<?php unset($__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal07cb24c7759400d4bcd39cc892e46f4c)): ?>
<?php $component = $__componentOriginal07cb24c7759400d4bcd39cc892e46f4c; ?>
<?php unset($__componentOriginal07cb24c7759400d4bcd39cc892e46f4c); ?>
<?php endif; ?>
        <?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $attributes = $__attributesOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__attributesOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $component = $__componentOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__componentOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>

<div class="customer-360__header-grid mb-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
    <div class="customer-360__meta">
        <span class="customer-360__meta-label"><?php echo e(__('Company')); ?></span>
        <span class="customer-360__meta-value"><?php echo e($header['company'] ?? '—'); ?></span>
    </div>
    <div class="customer-360__meta">
        <span class="customer-360__meta-label"><?php echo e(__('Branch')); ?></span>
        <span class="customer-360__meta-value"><?php echo e($header['branch'] ?? '—'); ?></span>
    </div>
    <div class="customer-360__meta">
        <span class="customer-360__meta-label"><?php echo e(__('Segment')); ?></span>
        <span class="customer-360__meta-value"><?php echo e($header['segments'] ? implode(', ', $header['segments']) : '—'); ?></span>
    </div>
    <div class="customer-360__meta">
        <span class="customer-360__meta-label"><?php echo e(__('Contact person')); ?></span>
        <span class="customer-360__meta-value"><?php echo e($header['contact_person'] ?? '—'); ?></span>
    </div>
    <div class="customer-360__meta">
        <span class="customer-360__meta-label"><?php echo e(__('Email')); ?></span>
        <span class="customer-360__meta-value truncate"><?php echo e($header['email'] ?? '—'); ?></span>
    </div>
    <div class="customer-360__meta">
        <span class="customer-360__meta-label"><?php echo e(__('Phone')); ?></span>
        <span class="customer-360__meta-value"><?php echo e($header['phone'] ?? '—'); ?></span>
    </div>
    <div class="customer-360__meta">
        <span class="customer-360__meta-label"><?php echo e(__('Credit limit')); ?></span>
        <span class="customer-360__meta-value tabular-nums"><?php echo e(number_format((float) $header['credit_limit'], 2)); ?></span>
    </div>
    <div class="customer-360__meta">
        <span class="customer-360__meta-label"><?php echo e(__('Payment terms')); ?></span>
        <span class="customer-360__meta-value"><?php echo e($header['payment_terms'] ?? '—'); ?></span>
    </div>
</div>

<?php if(count($quickActions) > 0): ?>
    <div class="customer-360__quick-actions mb-4 flex flex-wrap gap-2">
        <?php $__currentLoopData = $quickActions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a
                href="<?php echo e($action['url']); ?>"
                <?php if(! empty($action['scroll'])): ?> id="<?php echo e($action['scroll']); ?>" <?php endif; ?>
                class="erp-btn-secondary text-sm"
                data-turbo-frame="erp-main"
                data-turbo-action="advance"
            ><?php echo e($action['label']); ?></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\workspace\header.blade.php ENDPATH**/ ?>
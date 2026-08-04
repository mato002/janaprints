<?php
    use App\Support\Navigation\WorkspaceEmbed;
?>

<div
    x-data="{
        section: new URLSearchParams(window.location.search).get('setup') || 'leave-types',
        setSection(id) {
            this.section = id;
            const url = new URL(window.location.href);
            url.searchParams.set('tab', 'setup');
            url.searchParams.set('setup', id);
            window.history.replaceState({}, '', url);
        },
    }"
>
    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-5">
        <?php $__currentLoopData = [
            ['label' => __('Leave Types'), 'value' => $setupStats['leave_types']],
            ['label' => __('Holidays'), 'value' => $setupStats['holidays']],
            ['label' => __('Policies'), 'value' => $setupStats['policies']],
            ['label' => __('Accrual Rules'), 'value' => $setupStats['accrual_rules']],
            ['label' => __('Carry Rules'), 'value' => $setupStats['carry_rules']],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => $kpi['label'],'value' => $kpi['value']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpi['label']),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpi['value'])]); ?>
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

    <nav class="mb-4 flex flex-wrap gap-2 border-b border-slate-200 pb-2" aria-label="<?php echo e(__('Leave setup sections')); ?>">
        <?php $__currentLoopData = [
            'leave-types' => __('Leave Types'),
            'holidays' => __('Public Holidays'),
            'policies' => __('Leave Policies'),
            'accrual-rules' => __('Accrual Rules'),
            'carry-forward' => __('Carry Forward'),
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button
                type="button"
                class="rounded-md px-3 py-1.5 text-sm font-medium"
                :class="section === <?php echo \Illuminate\Support\Js::from($id)->toHtml() ?> ? 'bg-erp-primary text-white' : 'text-slate-600 hover:bg-slate-100'"
                @click="setSection(<?php echo \Illuminate\Support\Js::from($id)->toHtml() ?>)"
            ><?php echo e($label); ?></button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>

    <div x-show="section === 'leave-types'" class="space-y-4">
        <?php echo $__env->make('admin.hr.leave.config.tabs.leave-types', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
    <div x-show="section === 'holidays'" x-cloak class="space-y-4">
        <?php echo $__env->make('admin.hr.leave.config.tabs.holidays', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
    <div x-show="section === 'policies'" x-cloak class="space-y-4">
        <?php echo $__env->make('admin.hr.leave.config.tabs.policies', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
    <div x-show="section === 'accrual-rules'" x-cloak class="space-y-4">
        <?php echo $__env->make('admin.hr.leave.config.tabs.accrual-rules', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
    <div x-show="section === 'carry-forward'" x-cloak class="space-y-4">
        <?php echo $__env->make('admin.hr.leave.config.tabs.carry-forward', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\leave\partials\workspace-setup.blade.php ENDPATH**/ ?>
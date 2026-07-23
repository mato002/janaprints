<?php if (isset($component)) { $__componentOriginal9577df2686262fb25ceb19a81119823d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9577df2686262fb25ceb19a81119823d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.index-toolbar','data' => ['action' => route('admin.reports.operational-registers'),'resetUrl' => route('admin.reports.operational-registers', request()->only('embedded')),'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.index-toolbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.reports.operational-registers')),'reset-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.reports.operational-registers', request()->only('embedded'))),'compact' => true]); ?>
    <select id="preset" name="preset" class="erp-toolbar-select" aria-label="<?php echo e(__('Period')); ?>" data-erp-auto-submit>
        <option value=""><?php echo e(__('Custom range')); ?></option>
        <?php $__currentLoopData = $presets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($key); ?>" <?php if(($filters['preset'] ?? '') === $key): echo 'selected'; endif; ?>><?php echo e(__($label)); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>

    <input id="from_date" type="date" name="from_date" value="<?php echo e($filters['from_date'] ?? ''); ?>" class="erp-toolbar-input" aria-label="<?php echo e(__('From date')); ?>" data-erp-auto-submit>
    <input id="to_date" type="date" name="to_date" value="<?php echo e($filters['to_date'] ?? ''); ?>" class="erp-toolbar-input" aria-label="<?php echo e(__('To date')); ?>" data-erp-auto-submit>

    <select id="branch_id" name="branch_id" class="erp-toolbar-select" aria-label="<?php echo e(__('Branch')); ?>" data-erp-auto-submit>
        <option value=""><?php echo e(__('All branches')); ?></option>
        <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($branch->id); ?>" <?php if((string) ($filters['branch_id'] ?? '') === (string) $branch->id): echo 'selected'; endif; ?>><?php echo e($branch->name); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>

    <input type="hidden" name="register" value="<?php echo e($filters['register'] ?? request('register', 'daily_sales')); ?>">

    <input id="search" type="search" name="search" value="<?php echo e($filters['search'] ?? ''); ?>" class="erp-toolbar-input min-w-[8rem] flex-1" placeholder="<?php echo e(__('Search…')); ?>" aria-label="<?php echo e(__('Search')); ?>" data-erp-auto-search>

    <?php if(request('embedded')): ?>
        <input type="hidden" name="embedded" value="1">
    <?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9577df2686262fb25ceb19a81119823d)): ?>
<?php $attributes = $__attributesOriginal9577df2686262fb25ceb19a81119823d; ?>
<?php unset($__attributesOriginal9577df2686262fb25ceb19a81119823d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9577df2686262fb25ceb19a81119823d)): ?>
<?php $component = $__componentOriginal9577df2686262fb25ceb19a81119823d; ?>
<?php unset($__componentOriginal9577df2686262fb25ceb19a81119823d); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\reports\operational-registers\partials\filters.blade.php ENDPATH**/ ?>
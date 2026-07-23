<?php
    $moduleOptions = [
        '' => __('All modules'),
        'sales' => __('Sales'),
        'hr' => __('HR'),
        'storefront' => __('Storefront'),
        'system' => __('System'),
    ];
?>

<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['padding' => false,'class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'class' => 'mb-4']); ?>
    <?php if (isset($component)) { $__componentOriginal9577df2686262fb25ceb19a81119823d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9577df2686262fb25ceb19a81119823d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.index-toolbar','data' => ['action' => request()->url(),'resetUrl' => request()->url(),'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.index-toolbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->url()),'reset-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->url()),'compact' => true]); ?>
        <?php if(($viewMode ?? '') === 'queued'): ?>
            <?php if (isset($component)) { $__componentOriginal8f4fa08e40799e29ae21fbd8366f95e3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f4fa08e40799e29ae21fbd8366f95e3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.filter-pill-select','data' => ['name' => 'status','label' => __('Status'),'selected' => $filters['status'] ?? '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.filter-pill-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'status','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Status')),'selected' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filters['status'] ?? '')]); ?>
                <option value=""><?php echo e(__('All statuses')); ?></option>
                <?php $__currentLoopData = [App\Enums\EmailDeliveryStatus::Queued, App\Enums\EmailDeliveryStatus::Sending]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($status->value); ?>" <?php if(($filters['status'] ?? '') === $status->value): echo 'selected'; endif; ?>><?php echo e($status->label()); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8f4fa08e40799e29ae21fbd8366f95e3)): ?>
<?php $attributes = $__attributesOriginal8f4fa08e40799e29ae21fbd8366f95e3; ?>
<?php unset($__attributesOriginal8f4fa08e40799e29ae21fbd8366f95e3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8f4fa08e40799e29ae21fbd8366f95e3)): ?>
<?php $component = $__componentOriginal8f4fa08e40799e29ae21fbd8366f95e3; ?>
<?php unset($__componentOriginal8f4fa08e40799e29ae21fbd8366f95e3); ?>
<?php endif; ?>
        <?php elseif(($viewMode ?? '') === 'inbox'): ?>
            <?php if (isset($component)) { $__componentOriginal8f4fa08e40799e29ae21fbd8366f95e3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f4fa08e40799e29ae21fbd8366f95e3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.filter-pill-select','data' => ['name' => 'status','label' => __('Status'),'selected' => $filters['status'] ?? '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.filter-pill-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'status','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Status')),'selected' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filters['status'] ?? '')]); ?>
                <option value=""><?php echo e(__('All statuses')); ?></option>
                <?php $__currentLoopData = [App\Enums\EmailDeliveryStatus::Failed, App\Enums\EmailDeliveryStatus::Bounced]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($status->value); ?>" <?php if(($filters['status'] ?? '') === $status->value): echo 'selected'; endif; ?>><?php echo e($status->label()); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8f4fa08e40799e29ae21fbd8366f95e3)): ?>
<?php $attributes = $__attributesOriginal8f4fa08e40799e29ae21fbd8366f95e3; ?>
<?php unset($__attributesOriginal8f4fa08e40799e29ae21fbd8366f95e3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8f4fa08e40799e29ae21fbd8366f95e3)): ?>
<?php $component = $__componentOriginal8f4fa08e40799e29ae21fbd8366f95e3; ?>
<?php unset($__componentOriginal8f4fa08e40799e29ae21fbd8366f95e3); ?>
<?php endif; ?>
        <?php else: ?>
            <?php if (isset($component)) { $__componentOriginal8f4fa08e40799e29ae21fbd8366f95e3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f4fa08e40799e29ae21fbd8366f95e3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.filter-pill-select','data' => ['name' => 'status','label' => __('Status'),'selected' => $filters['status'] ?? '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.filter-pill-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'status','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Status')),'selected' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filters['status'] ?? '')]); ?>
                <option value=""><?php echo e(__('All statuses')); ?></option>
                <?php $__currentLoopData = [App\Enums\EmailDeliveryStatus::Sent, App\Enums\EmailDeliveryStatus::Delivered, App\Enums\EmailDeliveryStatus::Opened, App\Enums\EmailDeliveryStatus::Clicked]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($status->value); ?>" <?php if(($filters['status'] ?? '') === $status->value): echo 'selected'; endif; ?>><?php echo e($status->label()); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8f4fa08e40799e29ae21fbd8366f95e3)): ?>
<?php $attributes = $__attributesOriginal8f4fa08e40799e29ae21fbd8366f95e3; ?>
<?php unset($__attributesOriginal8f4fa08e40799e29ae21fbd8366f95e3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8f4fa08e40799e29ae21fbd8366f95e3)): ?>
<?php $component = $__componentOriginal8f4fa08e40799e29ae21fbd8366f95e3; ?>
<?php unset($__componentOriginal8f4fa08e40799e29ae21fbd8366f95e3); ?>
<?php endif; ?>
        <?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal8f4fa08e40799e29ae21fbd8366f95e3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f4fa08e40799e29ae21fbd8366f95e3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.filter-pill-select','data' => ['name' => 'sender','label' => __('Sender'),'selected' => $filters['sender'] ?? '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.filter-pill-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'sender','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Sender')),'selected' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filters['sender'] ?? '')]); ?>
            <option value=""><?php echo e(__('All senders')); ?></option>
            <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($account->id); ?>" <?php if(($filters['sender'] ?? '') == $account->id): echo 'selected'; endif; ?>><?php echo e($account->from_email); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8f4fa08e40799e29ae21fbd8366f95e3)): ?>
<?php $attributes = $__attributesOriginal8f4fa08e40799e29ae21fbd8366f95e3; ?>
<?php unset($__attributesOriginal8f4fa08e40799e29ae21fbd8366f95e3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8f4fa08e40799e29ae21fbd8366f95e3)): ?>
<?php $component = $__componentOriginal8f4fa08e40799e29ae21fbd8366f95e3; ?>
<?php unset($__componentOriginal8f4fa08e40799e29ae21fbd8366f95e3); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal8f4fa08e40799e29ae21fbd8366f95e3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f4fa08e40799e29ae21fbd8366f95e3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.filter-pill-select','data' => ['name' => 'module','label' => __('Module'),'selected' => $filters['module'] ?? '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.filter-pill-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'module','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Module')),'selected' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filters['module'] ?? '')]); ?>
            <?php $__currentLoopData = $moduleOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($value); ?>" <?php if(($filters['module'] ?? '') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8f4fa08e40799e29ae21fbd8366f95e3)): ?>
<?php $attributes = $__attributesOriginal8f4fa08e40799e29ae21fbd8366f95e3; ?>
<?php unset($__attributesOriginal8f4fa08e40799e29ae21fbd8366f95e3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8f4fa08e40799e29ae21fbd8366f95e3)): ?>
<?php $component = $__componentOriginal8f4fa08e40799e29ae21fbd8366f95e3; ?>
<?php unset($__componentOriginal8f4fa08e40799e29ae21fbd8366f95e3); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal8db0afd811ec0387bd5117b4a585cbd7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8db0afd811ec0387bd5117b4a585cbd7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.filter-pill-date','data' => ['name' => 'date_from','label' => __('From date'),'value' => $filters['date_from'] ?? '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.filter-pill-date'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'date_from','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('From date')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filters['date_from'] ?? '')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8db0afd811ec0387bd5117b4a585cbd7)): ?>
<?php $attributes = $__attributesOriginal8db0afd811ec0387bd5117b4a585cbd7; ?>
<?php unset($__attributesOriginal8db0afd811ec0387bd5117b4a585cbd7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8db0afd811ec0387bd5117b4a585cbd7)): ?>
<?php $component = $__componentOriginal8db0afd811ec0387bd5117b4a585cbd7; ?>
<?php unset($__componentOriginal8db0afd811ec0387bd5117b4a585cbd7); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal8db0afd811ec0387bd5117b4a585cbd7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8db0afd811ec0387bd5117b4a585cbd7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.filter-pill-date','data' => ['name' => 'date_to','label' => __('To date'),'value' => $filters['date_to'] ?? '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.filter-pill-date'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'date_to','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('To date')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filters['date_to'] ?? '')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8db0afd811ec0387bd5117b4a585cbd7)): ?>
<?php $attributes = $__attributesOriginal8db0afd811ec0387bd5117b4a585cbd7; ?>
<?php unset($__attributesOriginal8db0afd811ec0387bd5117b4a585cbd7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8db0afd811ec0387bd5117b4a585cbd7)): ?>
<?php $component = $__componentOriginal8db0afd811ec0387bd5117b4a585cbd7; ?>
<?php unset($__componentOriginal8db0afd811ec0387bd5117b4a585cbd7); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\email\partials\filters.blade.php ENDPATH**/ ?>
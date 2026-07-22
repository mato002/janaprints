<?php
    $record = $ticket ?? null;
    $fields = $formFields ?? [];
?>
<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <?php if(($fields['customer_id']['visible'] ?? true)): ?>
        <?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'customer_id','label' => $fields['customer_id']['label'] ?? __('Customer'),'options' => $customers,'value' => old('customer_id', $record?->customer_id),'required' => ($fields['customer_id']['required'] ?? false),'readonly' => ($fields['customer_id']['read_only'] ?? false),'createRoute' => 'admin.crm.customers.quick-create','refreshRoute' => 'admin.lookups.customers','permission' => 'crm.customers.create','modalTitle' => __('Create customer'),'optionLabelKey' => 'company_name','optionValueKey' => 'id','selectClass' => 'erp-input w-full','placeholder' => __('No customer linked'),'class' => 'md:col-span-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'customer_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['customer_id']['label'] ?? __('Customer')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($customers),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('customer_id', $record?->customer_id)),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['customer_id']['required'] ?? false)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['customer_id']['read_only'] ?? false)),'create-route' => 'admin.crm.customers.quick-create','refresh-route' => 'admin.lookups.customers','permission' => 'crm.customers.create','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create customer')),'option-label-key' => 'company_name','option-value-key' => 'id','select-class' => 'erp-input w-full','placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No customer linked')),'class' => 'md:col-span-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3)): ?>
<?php $attributes = $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3; ?>
<?php unset($__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3)): ?>
<?php $component = $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3; ?>
<?php unset($__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3); ?>
<?php endif; ?>
    <?php endif; ?>
    <?php if(($fields['subject']['visible'] ?? true)): ?>
        <div class="md:col-span-2">
            <label class="erp-label"><?php echo e($fields['subject']['label'] ?? __('Subject')); ?></label>
            <input type="text" name="subject" class="erp-input w-full" value="<?php echo e(old('subject', $record?->subject)); ?>" <?php if($fields['subject']['required'] ?? true): echo 'required'; endif; ?> <?php if($fields['subject']['read_only'] ?? false): echo 'readonly'; endif; ?>>
        </div>
    <?php endif; ?>
    <?php if(($fields['description']['visible'] ?? true)): ?>
        <div class="md:col-span-2">
            <label class="erp-label"><?php echo e($fields['description']['label'] ?? __('Description')); ?></label>
            <textarea name="description" class="erp-input w-full" rows="5" <?php if($fields['description']['required'] ?? true): echo 'required'; endif; ?> <?php if($fields['description']['read_only'] ?? false): echo 'readonly'; endif; ?>><?php echo e(old('description', $record?->description)); ?></textarea>
        </div>
    <?php endif; ?>
    <?php if(($fields['channel']['visible'] ?? true)): ?>
        <div>
            <label class="erp-label"><?php echo e($fields['channel']['label'] ?? __('Channel')); ?></label>
            <select name="channel" class="erp-input w-full" <?php if($fields['channel']['required'] ?? true): echo 'required'; endif; ?> <?php if($fields['channel']['read_only'] ?? false): echo 'disabled'; endif; ?>>
                <?php $__currentLoopData = App\Enums\CommercialTicketChannel::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $channel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($channel->value); ?>" <?php if(old('channel', $record?->channel?->value ?? 'phone') === $channel->value): echo 'selected'; endif; ?>><?php echo e($channel->label()); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
    <?php endif; ?>
    <?php if(($fields['priority']['visible'] ?? true)): ?>
        <div>
            <label class="erp-label"><?php echo e($fields['priority']['label'] ?? __('Priority')); ?></label>
            <select name="priority" class="erp-input w-full" <?php if($fields['priority']['required'] ?? true): echo 'required'; endif; ?> <?php if($fields['priority']['read_only'] ?? false): echo 'disabled'; endif; ?>>
                <?php $__currentLoopData = App\Enums\CommercialTicketPriority::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priority): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($priority->value); ?>" <?php if(old('priority', $record?->priority?->value ?? 'medium') === $priority->value): echo 'selected'; endif; ?>><?php echo e($priority->label()); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
    <?php endif; ?>
</div>
<?php echo $__env->make('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => $record ?? null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\commercial\support-tickets\form.blade.php ENDPATH**/ ?>
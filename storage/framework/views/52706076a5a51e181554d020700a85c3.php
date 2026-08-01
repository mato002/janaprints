<?php ($fields = $formFields ?? []); ?>
<div class="erp-form-grid">
    <?php if(($fields['company_name']['visible'] ?? true)): ?>
    <?php if (isset($component)) { $__componentOriginal187e7f8ae725d0d7c586a97e85953c03 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.input','data' => ['name' => 'company_name','label' => __('Company name'),'value' => old('company_name', $customer?->company_name ?? ($fields['company_name']['default'] ?? '')),'required' => ($fields['company_name']['required'] ?? false),'readonly' => ($fields['company_name']['read_only'] ?? false)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'company_name','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Company name')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('company_name', $customer?->company_name ?? ($fields['company_name']['default'] ?? ''))),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['company_name']['required'] ?? false)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['company_name']['read_only'] ?? false))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $attributes = $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $component = $__componentOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if(($fields['customer_type']['visible'] ?? true)): ?>
    <?php if (isset($component)) { $__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-field','data' => ['name' => 'customer_type','label' => __('Type'),'required' => ($fields['customer_type']['required'] ?? true),'readonly' => ($fields['customer_type']['read_only'] ?? false)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'customer_type','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Type')),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['customer_type']['required'] ?? true)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['customer_type']['read_only'] ?? false))]); ?>
        <select name="customer_type" class="erp-select w-full" <?php if($fields['customer_type']['required'] ?? true): echo 'required'; endif; ?> <?php if($fields['customer_type']['read_only'] ?? false): echo 'disabled'; endif; ?>>
            <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($type->value); ?>" <?php if(old('customer_type', $customer?->customer_type?->value ?? ($fields['customer_type']['default'] ?? null)) === $type->value): echo 'selected'; endif; ?>><?php echo e($type->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1)): ?>
<?php $attributes = $__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1; ?>
<?php unset($__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1)): ?>
<?php $component = $__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1; ?>
<?php unset($__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if(($fields['contact_person']['visible'] ?? true)): ?>
    <?php if (isset($component)) { $__componentOriginal187e7f8ae725d0d7c586a97e85953c03 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.input','data' => ['name' => 'contact_person','label' => __('Contact person'),'value' => old('contact_person', $customer?->contact_person ?? ($fields['contact_person']['default'] ?? '')),'required' => ($fields['contact_person']['required'] ?? false),'readonly' => ($fields['contact_person']['read_only'] ?? false)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'contact_person','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Contact person')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('contact_person', $customer?->contact_person ?? ($fields['contact_person']['default'] ?? ''))),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['contact_person']['required'] ?? false)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['contact_person']['read_only'] ?? false))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $attributes = $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $component = $__componentOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if(($fields['phone']['visible'] ?? true)): ?>
    <?php if (isset($component)) { $__componentOriginal187e7f8ae725d0d7c586a97e85953c03 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.input','data' => ['name' => 'phone','label' => __('Phone'),'value' => old('phone', $customer?->phone ?? ($fields['phone']['default'] ?? '')),'required' => ($fields['phone']['required'] ?? false),'readonly' => ($fields['phone']['read_only'] ?? false)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'phone','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Phone')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('phone', $customer?->phone ?? ($fields['phone']['default'] ?? ''))),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['phone']['required'] ?? false)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['phone']['read_only'] ?? false))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $attributes = $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $component = $__componentOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if(($fields['email']['visible'] ?? true)): ?>
    <?php if (isset($component)) { $__componentOriginal187e7f8ae725d0d7c586a97e85953c03 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.input','data' => ['name' => 'email','type' => 'email','label' => __('Email'),'value' => old('email', $customer?->email ?? ($fields['email']['default'] ?? '')),'required' => ($fields['email']['required'] ?? false),'readonly' => ($fields['email']['read_only'] ?? false)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'email','type' => 'email','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Email')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('email', $customer?->email ?? ($fields['email']['default'] ?? ''))),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['email']['required'] ?? false)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['email']['read_only'] ?? false))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $attributes = $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $component = $__componentOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if(($fields['kra_pin']['visible'] ?? true)): ?>
    <?php if (isset($component)) { $__componentOriginal187e7f8ae725d0d7c586a97e85953c03 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.input','data' => ['name' => 'kra_pin','label' => __('KRA PIN'),'value' => old('kra_pin', $customer?->kra_pin ?? ($fields['kra_pin']['default'] ?? '')),'required' => ($fields['kra_pin']['required'] ?? false),'readonly' => ($fields['kra_pin']['read_only'] ?? false)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'kra_pin','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('KRA PIN')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('kra_pin', $customer?->kra_pin ?? ($fields['kra_pin']['default'] ?? ''))),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['kra_pin']['required'] ?? false)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['kra_pin']['read_only'] ?? false))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $attributes = $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $component = $__componentOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if(($fields['website']['visible'] ?? true)): ?>
    <?php if (isset($component)) { $__componentOriginal187e7f8ae725d0d7c586a97e85953c03 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.input','data' => ['name' => 'website','label' => __('Website'),'value' => old('website', $customer?->website ?? ($fields['website']['default'] ?? '')),'required' => ($fields['website']['required'] ?? false),'readonly' => ($fields['website']['read_only'] ?? false)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'website','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Website')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('website', $customer?->website ?? ($fields['website']['default'] ?? ''))),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['website']['required'] ?? false)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['website']['read_only'] ?? false))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $attributes = $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $component = $__componentOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if(($fields['physical_address']['visible'] ?? true)): ?>
    <?php if (isset($component)) { $__componentOriginal694712473b787cd740db4e46be9da3f9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal694712473b787cd740db4e46be9da3f9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.textarea','data' => ['name' => 'physical_address','label' => __('Physical address'),'value' => old('physical_address', $customer?->physical_address ?? ($fields['physical_address']['default'] ?? '')),'required' => ($fields['physical_address']['required'] ?? false),'readonly' => ($fields['physical_address']['read_only'] ?? false)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'physical_address','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Physical address')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('physical_address', $customer?->physical_address ?? ($fields['physical_address']['default'] ?? ''))),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['physical_address']['required'] ?? false)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['physical_address']['read_only'] ?? false))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal694712473b787cd740db4e46be9da3f9)): ?>
<?php $attributes = $__attributesOriginal694712473b787cd740db4e46be9da3f9; ?>
<?php unset($__attributesOriginal694712473b787cd740db4e46be9da3f9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal694712473b787cd740db4e46be9da3f9)): ?>
<?php $component = $__componentOriginal694712473b787cd740db4e46be9da3f9; ?>
<?php unset($__componentOriginal694712473b787cd740db4e46be9da3f9); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if(($fields['city']['visible'] ?? true)): ?>
    <?php if (isset($component)) { $__componentOriginal187e7f8ae725d0d7c586a97e85953c03 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.input','data' => ['name' => 'city','label' => __('City'),'value' => old('city', $customer?->city ?? ($fields['city']['default'] ?? '')),'required' => ($fields['city']['required'] ?? false),'readonly' => ($fields['city']['read_only'] ?? false)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'city','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('City')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('city', $customer?->city ?? ($fields['city']['default'] ?? ''))),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['city']['required'] ?? false)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['city']['read_only'] ?? false))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $attributes = $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $component = $__componentOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if(($fields['credit_limit']['visible'] ?? true)): ?>
    <?php if (isset($component)) { $__componentOriginal187e7f8ae725d0d7c586a97e85953c03 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.input','data' => ['name' => 'credit_limit','type' => 'number','label' => __('Credit limit'),'value' => old('credit_limit', $customer?->credit_limit ?? ($fields['credit_limit']['default'] ?? 0)),'required' => ($fields['credit_limit']['required'] ?? false),'readonly' => ($fields['credit_limit']['read_only'] ?? false),'step' => '0.01']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'credit_limit','type' => 'number','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Credit limit')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('credit_limit', $customer?->credit_limit ?? ($fields['credit_limit']['default'] ?? 0))),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['credit_limit']['required'] ?? false)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['credit_limit']['read_only'] ?? false)),'step' => '0.01']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $attributes = $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $component = $__componentOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if(($fields['payment_terms']['visible'] ?? true)): ?>
    <?php if (isset($component)) { $__componentOriginal187e7f8ae725d0d7c586a97e85953c03 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.input','data' => ['name' => 'payment_terms','label' => __('Payment terms'),'value' => old('payment_terms', $customer?->payment_terms ?? ($fields['payment_terms']['default'] ?? '')),'required' => ($fields['payment_terms']['required'] ?? false),'readonly' => ($fields['payment_terms']['read_only'] ?? false)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'payment_terms','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Payment terms')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('payment_terms', $customer?->payment_terms ?? ($fields['payment_terms']['default'] ?? ''))),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['payment_terms']['required'] ?? false)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['payment_terms']['read_only'] ?? false))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $attributes = $__attributesOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__attributesOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03)): ?>
<?php $component = $__componentOriginal187e7f8ae725d0d7c586a97e85953c03; ?>
<?php unset($__componentOriginal187e7f8ae725d0d7c586a97e85953c03); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if($customer && ($fields['status']['visible'] ?? true)): ?>
        <?php if (isset($component)) { $__componentOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-status-select','data' => ['formKey' => 'customer','field' => $fields['status'],'value' => $customer?->status,'model' => $customer,'selectClass' => 'erp-select w-full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-status-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['form-key' => 'customer','field' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($fields['status']),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($customer?->status),'model' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($customer),'select-class' => 'erp-select w-full']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8)): ?>
<?php $attributes = $__attributesOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8; ?>
<?php unset($__attributesOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8)): ?>
<?php $component = $__componentOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8; ?>
<?php unset($__componentOriginal5241fb5e5c5b0c16bbe1a9845f1ec8a8); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if(auth()->user()->hasRole('Super Admin') && ! $customer && ! tenant()->hasCompany()): ?>
        <?php if (isset($component)) { $__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-field','data' => ['name' => 'company_id','label' => __('Company'),'required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'company_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Company')),'required' => true]); ?>
            <select name="company_id" class="erp-select w-full" required>
                <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1)): ?>
<?php $attributes = $__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1; ?>
<?php unset($__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1)): ?>
<?php $component = $__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1; ?>
<?php unset($__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-field','data' => ['name' => 'branch_id','label' => __('Branch'),'required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'branch_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Branch')),'required' => true]); ?>
            <select name="branch_id" class="erp-select w-full" required>
                <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($b->id); ?>"><?php echo e($b->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1)): ?>
<?php $attributes = $__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1; ?>
<?php unset($__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1)): ?>
<?php $component = $__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1; ?>
<?php unset($__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if(($fields['segment_ids']['visible'] ?? true)): ?>
    <?php if (isset($component)) { $__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-field','data' => ['name' => 'segment_ids','label' => __('Segments'),'colSpan' => 2]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'segment_ids','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Segments')),'colSpan' => 2]); ?>
        <div class="flex flex-wrap gap-2">
            <?php $__currentLoopData = $segments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $segment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="inline-flex items-center gap-1 text-sm">
                    <input type="checkbox" name="segment_ids[]" value="<?php echo e($segment->id); ?>" <?php if(in_array($segment->id, old('segment_ids', $customer?->segments->pluck('id')->all() ?? []))): echo 'checked'; endif; ?> <?php if($fields['segment_ids']['read_only'] ?? false): echo 'disabled'; endif; ?>>
                    <?php echo e($segment->name); ?>

                </label>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1)): ?>
<?php $attributes = $__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1; ?>
<?php unset($__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1)): ?>
<?php $component = $__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1; ?>
<?php unset($__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if(($fields['notes']['visible'] ?? true)): ?>
    <?php if (isset($component)) { $__componentOriginal694712473b787cd740db4e46be9da3f9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal694712473b787cd740db4e46be9da3f9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.textarea','data' => ['name' => 'notes','label' => __('Notes'),'value' => old('notes', $customer?->notes ?? ($fields['notes']['default'] ?? '')),'required' => ($fields['notes']['required'] ?? false),'readonly' => ($fields['notes']['read_only'] ?? false)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'notes','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Notes')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('notes', $customer?->notes ?? ($fields['notes']['default'] ?? ''))),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['notes']['required'] ?? false)),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($fields['notes']['read_only'] ?? false))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal694712473b787cd740db4e46be9da3f9)): ?>
<?php $attributes = $__attributesOriginal694712473b787cd740db4e46be9da3f9; ?>
<?php unset($__attributesOriginal694712473b787cd740db4e46be9da3f9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal694712473b787cd740db4e46be9da3f9)): ?>
<?php $component = $__componentOriginal694712473b787cd740db4e46be9da3f9; ?>
<?php unset($__componentOriginal694712473b787cd740db4e46be9da3f9); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php echo $__env->make('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => $customer ?? null, 'formKey' => 'customer'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/crm/customers/form.blade.php ENDPATH**/ ?>
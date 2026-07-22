<?php
    $inquiryTypes = config('conversion.inquiry_types');
?>

<?php if($errors->any()): ?>
    <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
        <p class="font-semibold"><?php echo e(__('Please correct the following and try again:')); ?></p>
        <ul class="mt-2 list-disc pl-5">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>

<form
    class="public-conversion-form public-conversion-form--light"
    data-contact-form
    method="POST"
    action="<?php echo e(route('public.contact-messages.store')); ?>"
    novalidate
>
    <?php echo csrf_field(); ?>
    <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="sr-only" aria-hidden="true">
    <input type="text" name="_gotcha" value="" tabindex="-1" autocomplete="off" class="sr-only" aria-hidden="true">

    <div class="public-conversion-form__grid">
        <?php if (isset($component)) { $__componentOriginal25c819b7b1b36c56f849b8e0a118adb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25c819b7b1b36c56f849b8e0a118adb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.form-floating-field','data' => ['id' => 'contact-name','name' => 'name','label' => 'Name','required' => true,'autocomplete' => 'name','maxlength' => '120']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.form-floating-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'contact-name','name' => 'name','label' => 'Name','required' => true,'autocomplete' => 'name','maxlength' => '120']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25c819b7b1b36c56f849b8e0a118adb9)): ?>
<?php $attributes = $__attributesOriginal25c819b7b1b36c56f849b8e0a118adb9; ?>
<?php unset($__attributesOriginal25c819b7b1b36c56f849b8e0a118adb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25c819b7b1b36c56f849b8e0a118adb9)): ?>
<?php $component = $__componentOriginal25c819b7b1b36c56f849b8e0a118adb9; ?>
<?php unset($__componentOriginal25c819b7b1b36c56f849b8e0a118adb9); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal25c819b7b1b36c56f849b8e0a118adb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25c819b7b1b36c56f849b8e0a118adb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.form-floating-field','data' => ['id' => 'contact-company','name' => 'company','label' => 'Company','optional' => true,'autocomplete' => 'organization','maxlength' => '160']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.form-floating-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'contact-company','name' => 'company','label' => 'Company','optional' => true,'autocomplete' => 'organization','maxlength' => '160']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25c819b7b1b36c56f849b8e0a118adb9)): ?>
<?php $attributes = $__attributesOriginal25c819b7b1b36c56f849b8e0a118adb9; ?>
<?php unset($__attributesOriginal25c819b7b1b36c56f849b8e0a118adb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25c819b7b1b36c56f849b8e0a118adb9)): ?>
<?php $component = $__componentOriginal25c819b7b1b36c56f849b8e0a118adb9; ?>
<?php unset($__componentOriginal25c819b7b1b36c56f849b8e0a118adb9); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal25c819b7b1b36c56f849b8e0a118adb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25c819b7b1b36c56f849b8e0a118adb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.form-floating-field','data' => ['id' => 'contact-phone','name' => 'phone','label' => 'Phone','type' => 'tel','optional' => true,'autocomplete' => 'tel','inputmode' => 'tel','maxlength' => '20']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.form-floating-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'contact-phone','name' => 'phone','label' => 'Phone','type' => 'tel','optional' => true,'autocomplete' => 'tel','inputmode' => 'tel','maxlength' => '20']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25c819b7b1b36c56f849b8e0a118adb9)): ?>
<?php $attributes = $__attributesOriginal25c819b7b1b36c56f849b8e0a118adb9; ?>
<?php unset($__attributesOriginal25c819b7b1b36c56f849b8e0a118adb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25c819b7b1b36c56f849b8e0a118adb9)): ?>
<?php $component = $__componentOriginal25c819b7b1b36c56f849b8e0a118adb9; ?>
<?php unset($__componentOriginal25c819b7b1b36c56f849b8e0a118adb9); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal25c819b7b1b36c56f849b8e0a118adb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25c819b7b1b36c56f849b8e0a118adb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.form-floating-field','data' => ['id' => 'contact-email','name' => 'email','label' => 'Email','type' => 'email','required' => true,'autocomplete' => 'email','maxlength' => '160']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.form-floating-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'contact-email','name' => 'email','label' => 'Email','type' => 'email','required' => true,'autocomplete' => 'email','maxlength' => '160']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25c819b7b1b36c56f849b8e0a118adb9)): ?>
<?php $attributes = $__attributesOriginal25c819b7b1b36c56f849b8e0a118adb9; ?>
<?php unset($__attributesOriginal25c819b7b1b36c56f849b8e0a118adb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25c819b7b1b36c56f849b8e0a118adb9)): ?>
<?php $component = $__componentOriginal25c819b7b1b36c56f849b8e0a118adb9; ?>
<?php unset($__componentOriginal25c819b7b1b36c56f849b8e0a118adb9); ?>
<?php endif; ?>

        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'public-field-float',
            'public-field-float--select',
            'public-conversion-form__field',
            'public-conversion-form__field--full',
            'is-filled' => filled(old('subject')),
        ]); ?>">
            <select id="contact-inquiry-type" name="subject" required data-contact-inquiry-type>
                <option value="" disabled <?php if(! old('subject')): echo 'selected'; endif; ?>>Select inquiry type</option>
                <?php $__currentLoopData = $inquiryTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($type['value']); ?>" data-inquiry-slug="<?php echo e($type['slug']); ?>" <?php if(old('subject') === $type['value']): echo 'selected'; endif; ?>>
                        <?php echo e($type['value']); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <label for="contact-inquiry-type">
                Inquiry Type
                <span class="public-field-float__required" aria-hidden="true">*</span>
            </label>
            <p class="mt-2 text-xs text-brand-text-muted" data-contact-quote-hint hidden>
                For detailed pricing, use our
                <a href="<?php echo e($quoteFormHref); ?>" class="font-semibold text-brand-magenta hover:text-brand-magenta-hover">quote request form</a>.
            </p>
        </div>

        <?php if (isset($component)) { $__componentOriginal25c819b7b1b36c56f849b8e0a118adb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25c819b7b1b36c56f849b8e0a118adb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.form-floating-field','data' => ['id' => 'contact-message','name' => 'message','label' => 'Message','type' => 'textarea','rows' => 5,'required' => true,'full' => true,'maxlength' => '3000']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.form-floating-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'contact-message','name' => 'message','label' => 'Message','type' => 'textarea','rows' => 5,'required' => true,'full' => true,'maxlength' => '3000']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25c819b7b1b36c56f849b8e0a118adb9)): ?>
<?php $attributes = $__attributesOriginal25c819b7b1b36c56f849b8e0a118adb9; ?>
<?php unset($__attributesOriginal25c819b7b1b36c56f849b8e0a118adb9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25c819b7b1b36c56f849b8e0a118adb9)): ?>
<?php $component = $__componentOriginal25c819b7b1b36c56f849b8e0a118adb9; ?>
<?php unset($__componentOriginal25c819b7b1b36c56f849b8e0a118adb9); ?>
<?php endif; ?>
    </div>

    <div class="public-conversion-form__submit">
        <?php if (isset($component)) { $__componentOriginalab8f5d6997167bf1906093658ed3789d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab8f5d6997167bf1906093658ed3789d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.button','data' => ['type' => 'submit','variant' => 'gradient','size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','variant' => 'gradient','size' => 'lg']); ?>Send Message <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab8f5d6997167bf1906093658ed3789d)): ?>
<?php $attributes = $__attributesOriginalab8f5d6997167bf1906093658ed3789d; ?>
<?php unset($__attributesOriginalab8f5d6997167bf1906093658ed3789d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab8f5d6997167bf1906093658ed3789d)): ?>
<?php $component = $__componentOriginalab8f5d6997167bf1906093658ed3789d; ?>
<?php unset($__componentOriginalab8f5d6997167bf1906093658ed3789d); ?>
<?php endif; ?>
    </div>

    <?php if(session('contact_success')): ?>
        <div class="public-conversion-form__success" data-contact-success role="status">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p><strong>Thank you!</strong> <?php echo e(session('contact_success')); ?></p>
        </div>
    <?php else: ?>
        <div class="public-conversion-form__success" data-contact-success role="status" hidden>
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p data-contact-success-text><strong>Thank you!</strong> <span></span></p>
        </div>
    <?php endif; ?>
</form>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\public\contact-form.blade.php ENDPATH**/ ?>
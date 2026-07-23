<?php
    $services = config('conversion.services');
?>

<section
    id="quote-form"
    class="public-quote-section public-section public-dot-pattern"
    data-testid="homepage-quote-form"
    data-reveal-section
    aria-label="Request a quote"
>
    <div class="public-container">
        <div class="public-quote-section__intro" data-animate="fade-up">
            <?php if (isset($component)) { $__componentOriginalf78519ad210db5d56149fa0f4b185795 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf78519ad210db5d56149fa0f4b185795 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.section-heading','data' => ['badge' => 'Request a Quote','title' => 'Get Your Free Quotation','description' => 'Tell us about your project and our team will respond with pricing and guidance.','align' => 'center','class' => '!mb-0 max-w-3xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.section-heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['badge' => 'Request a Quote','title' => 'Get Your Free Quotation','description' => 'Tell us about your project and our team will respond with pricing and guidance.','align' => 'center','class' => '!mb-0 max-w-3xl']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf78519ad210db5d56149fa0f4b185795)): ?>
<?php $attributes = $__attributesOriginalf78519ad210db5d56149fa0f4b185795; ?>
<?php unset($__attributesOriginalf78519ad210db5d56149fa0f4b185795); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf78519ad210db5d56149fa0f4b185795)): ?>
<?php $component = $__componentOriginalf78519ad210db5d56149fa0f4b185795; ?>
<?php unset($__componentOriginalf78519ad210db5d56149fa0f4b185795); ?>
<?php endif; ?>
        </div>

        <div class="public-quote-section__card public-card public-card--soft public-card--static" data-animate="fade-up" data-animate-delay="1">
            <?php if($errors->any()): ?>
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
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
                data-quote-form
                method="POST"
                action="<?php echo e(route('public.quote-requests.store')); ?>"
                enctype="multipart/form-data"
                novalidate
            >
                <?php echo csrf_field(); ?>
                <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="sr-only" aria-hidden="true">
                <input type="text" name="_gotcha" value="" tabindex="-1" autocomplete="off" class="sr-only" aria-hidden="true">

                <div class="public-conversion-form__grid">
                    <?php if (isset($component)) { $__componentOriginal25c819b7b1b36c56f849b8e0a118adb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25c819b7b1b36c56f849b8e0a118adb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.form-floating-field','data' => ['id' => 'quote-name','name' => 'name','label' => 'Name','required' => true,'autocomplete' => 'name','maxlength' => '120']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.form-floating-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'quote-name','name' => 'name','label' => 'Name','required' => true,'autocomplete' => 'name','maxlength' => '120']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.form-floating-field','data' => ['id' => 'quote-company','name' => 'company','label' => 'Company','optional' => true,'autocomplete' => 'organization','maxlength' => '160']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.form-floating-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'quote-company','name' => 'company','label' => 'Company','optional' => true,'autocomplete' => 'organization','maxlength' => '160']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.form-floating-field','data' => ['id' => 'quote-phone','name' => 'phone','label' => 'Phone','type' => 'tel','required' => true,'autocomplete' => 'tel','inputmode' => 'tel','maxlength' => '20']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.form-floating-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'quote-phone','name' => 'phone','label' => 'Phone','type' => 'tel','required' => true,'autocomplete' => 'tel','inputmode' => 'tel','maxlength' => '20']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.form-floating-field','data' => ['id' => 'quote-email','name' => 'email','label' => 'Email','type' => 'email','required' => true,'autocomplete' => 'email','maxlength' => '160']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.form-floating-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'quote-email','name' => 'email','label' => 'Email','type' => 'email','required' => true,'autocomplete' => 'email','maxlength' => '160']); ?>
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
                    <?php if (isset($component)) { $__componentOriginal77e22827a7b147ee5454cac191d946aa = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal77e22827a7b147ee5454cac191d946aa = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.form-floating-select','data' => ['id' => 'quote-service','name' => 'service','label' => 'Service Needed','required' => true,'placeholder' => 'Select a service','options' => $services,'value' => old('service', old('service_needed'))]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.form-floating-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'quote-service','name' => 'service','label' => 'Service Needed','required' => true,'placeholder' => 'Select a service','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($services),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('service', old('service_needed')))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal77e22827a7b147ee5454cac191d946aa)): ?>
<?php $attributes = $__attributesOriginal77e22827a7b147ee5454cac191d946aa; ?>
<?php unset($__attributesOriginal77e22827a7b147ee5454cac191d946aa); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal77e22827a7b147ee5454cac191d946aa)): ?>
<?php $component = $__componentOriginal77e22827a7b147ee5454cac191d946aa; ?>
<?php unset($__componentOriginal77e22827a7b147ee5454cac191d946aa); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal25c819b7b1b36c56f849b8e0a118adb9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25c819b7b1b36c56f849b8e0a118adb9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.form-floating-field','data' => ['id' => 'quote-quantity','name' => 'quantity','label' => 'Quantity','optional' => true,'maxlength' => '80']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.form-floating-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'quote-quantity','name' => 'quantity','label' => 'Quantity','optional' => true,'maxlength' => '80']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.form-floating-field','data' => ['id' => 'quote-deadline','name' => 'deadline','label' => 'Deadline','optional' => true,'full' => true,'maxlength' => '80']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.form-floating-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'quote-deadline','name' => 'deadline','label' => 'Deadline','optional' => true,'full' => true,'maxlength' => '80']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.form-floating-field','data' => ['id' => 'quote-message','name' => 'message','label' => 'Message','type' => 'textarea','rows' => 4,'required' => true,'full' => true,'maxlength' => '3000']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.form-floating-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'quote-message','name' => 'message','label' => 'Message','type' => 'textarea','rows' => 4,'required' => true,'full' => true,'maxlength' => '3000']); ?>
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

                    <div class="public-conversion-form__field public-conversion-form__field--full">
                        <p class="public-conversion-form__upload-label">
                            Artwork Upload
                            <span class="public-field-float__optional">(optional)</span>
                        </p>
                        <div class="public-conversion-form__upload" data-upload-placeholder>
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <p>Drag &amp; drop artwork files here, or click to browse</p>
                            <span>PDF, AI, EPS, PSD, JPG, PNG, SVG — max 25 MB</span>
                            <input type="file" name="artwork" accept=".pdf,.ai,.eps,.psd,.jpg,.jpeg,.png,.svg" class="sr-only" data-artwork-input>
                        </div>
                        <p class="public-conversion-form__upload-note">Not required — attach artwork only if you already have files ready.</p>
                    </div>
                </div>

                <div class="public-conversion-form__submit public-conversion-form__submit--quote">
                    <?php if (isset($component)) { $__componentOriginalab8f5d6997167bf1906093658ed3789d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab8f5d6997167bf1906093658ed3789d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.button','data' => ['type' => 'submit','variant' => 'gradient','size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','variant' => 'gradient','size' => 'lg']); ?>
                        Submit Quote Request
                     <?php echo $__env->renderComponent(); ?>
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

                <?php if(session('quote_success')): ?>
                    <div class="public-conversion-form__success" data-quote-success role="status">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p><strong>Thank you!</strong> <?php echo e(session('quote_success')); ?></p>
                    </div>
                <?php else: ?>
                    <div class="public-conversion-form__success" data-quote-success role="status" hidden>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p data-quote-success-text><strong>Thank you!</strong> <span></span></p>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/public/quote-form.blade.php ENDPATH**/ ?>
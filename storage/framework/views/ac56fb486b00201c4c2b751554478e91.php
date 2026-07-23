<?php
    $whatsappUrl = 'https://wa.me/' . $whatsapp['number'] . '?text=' . rawurlencode($whatsapp['message']);
    $local = config('site.local');
?>

<?php if (isset($component)) { $__componentOriginal8c0e86a062c1c5bb6d0e151b7076f3fd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c0e86a062c1c5bb6d0e151b7076f3fd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.public','data' => ['seo' => $seo]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.public'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['seo' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($seo)]); ?>
    <?php if (isset($component)) { $__componentOriginal7667e390c55120bda1fc27c0189959e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7667e390c55120bda1fc27c0189959e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.page-hero','data' => ['title' => 'Contact Jana Prints','intro' => 'Reach our Nairobi team for printing enquiries, quotes, artwork support and nationwide delivery questions.','badge' => 'Contact','breadcrumbs' => $breadcrumbs]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.page-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Contact Jana Prints','intro' => 'Reach our Nairobi team for printing enquiries, quotes, artwork support and nationwide delivery questions.','badge' => 'Contact','breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($breadcrumbs)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7667e390c55120bda1fc27c0189959e9)): ?>
<?php $attributes = $__attributesOriginal7667e390c55120bda1fc27c0189959e9; ?>
<?php unset($__attributesOriginal7667e390c55120bda1fc27c0189959e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7667e390c55120bda1fc27c0189959e9)): ?>
<?php $component = $__componentOriginal7667e390c55120bda1fc27c0189959e9; ?>
<?php unset($__componentOriginal7667e390c55120bda1fc27c0189959e9); ?>
<?php endif; ?>

    <section class="public-section">
        <div class="public-container">
            <div class="grid gap-8 lg:grid-cols-2">
                <div class="public-card public-card--soft p-8" data-animate="fade-up">
                    <h2 class="font-display text-2xl font-bold">Get in touch</h2>
                    <dl class="mt-6 space-y-4 text-sm">
                        <div>
                            <dt class="font-semibold">Phone</dt>
                            <dd><a href="<?php echo e($contact['phone_href']); ?>"><?php echo e($local['phone'] ?: $contact['phone']); ?></a></dd>
                        </div>
                        <div>
                            <dt class="font-semibold">Email</dt>
                            <dd><a href="<?php echo e($contact['email_href']); ?>"><?php echo e($local['email'] ?: $contact['email']); ?></a></dd>
                        </div>
                        <div>
                            <dt class="font-semibold">Address</dt>
                            <dd><?php echo e($local['address'] ?: $contact['address']); ?>, <?php echo e($local['city']); ?>, <?php echo e($local['country']); ?></dd>
                        </div>
                        <div>
                            <dt class="font-semibold">Hours</dt>
                            <dd><?php echo e($contact['hours']); ?><br><?php echo e($contact['hours_weekend']); ?></dd>
                        </div>
                        <div>
                            <dt class="font-semibold">WhatsApp</dt>
                            <dd><a href="<?php echo e($whatsappUrl); ?>" target="_blank" rel="noopener noreferrer">Chat on WhatsApp</a></dd>
                        </div>
                    </dl>
                </div>

                <div class="public-card public-card--soft p-8" data-animate="fade-up">
                    <h2 class="font-display text-2xl font-bold">Send us a message</h2>
                    <p class="mt-2 text-sm text-brand-text-secondary">We typically respond within one business day.</p>

                    <div class="mt-6">
                        <?php if (isset($component)) { $__componentOriginal277836392961deb2d645cd3960d68b55 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal277836392961deb2d645cd3960d68b55 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.contact-form','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.contact-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal277836392961deb2d645cd3960d68b55)): ?>
<?php $attributes = $__attributesOriginal277836392961deb2d645cd3960d68b55; ?>
<?php unset($__attributesOriginal277836392961deb2d645cd3960d68b55); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal277836392961deb2d645cd3960d68b55)): ?>
<?php $component = $__componentOriginal277836392961deb2d645cd3960d68b55; ?>
<?php unset($__componentOriginal277836392961deb2d645cd3960d68b55); ?>
<?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="mt-8 public-card public-card--soft p-8" data-animate="fade-up">
                <h2 class="font-display text-2xl font-bold">Service areas</h2>
                <p class="mt-4 text-sm leading-relaxed text-brand-text-secondary">
                    We serve clients across Kenya with collection from Nairobi and nationwide delivery for campaigns, institutions and recurring print needs.
                </p>
                <ul class="mt-6 flex flex-wrap gap-2">
                    <?php $__currentLoopData = $local['service_areas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $area): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="public-footer__badge"><?php echo e($area); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
                <div class="mt-8">
                    <?php if (isset($component)) { $__componentOriginalab8f5d6997167bf1906093658ed3789d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab8f5d6997167bf1906093658ed3789d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.button','data' => ['href' => ''.e($quoteFormHref).'','variant' => 'gradient','size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e($quoteFormHref).'','variant' => 'gradient','size' => 'lg']); ?>Request a Quote <?php echo $__env->renderComponent(); ?>
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
            </div>
        </div>
    </section>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c0e86a062c1c5bb6d0e151b7076f3fd)): ?>
<?php $attributes = $__attributesOriginal8c0e86a062c1c5bb6d0e151b7076f3fd; ?>
<?php unset($__attributesOriginal8c0e86a062c1c5bb6d0e151b7076f3fd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c0e86a062c1c5bb6d0e151b7076f3fd)): ?>
<?php $component = $__componentOriginal8c0e86a062c1c5bb6d0e151b7076f3fd; ?>
<?php unset($__componentOriginal8c0e86a062c1c5bb6d0e151b7076f3fd); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\storefront\contact.blade.php ENDPATH**/ ?>
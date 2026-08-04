<?php
    $contact = config('conversion.contact');
    $whatsapp = config('conversion.whatsapp');
    $primaryCta = config('conversion.primary_cta');
    $actionCards = config('conversion.action_cards');
    $services = config('conversion.services');
    $faq = config('conversion.faq');
    $trustStrip = config('conversion.trust_strip');
    $branches = config('conversion.branches');

    $whatsappUrl = 'https://wa.me/' . $whatsapp['number'] . '?text=' . rawurlencode($whatsapp['message']);
?>

<section id="contact" class="public-conversion" data-reveal-section aria-label="Contact and quote request">

    
    <div class="public-conversion__header public-section bg-brand-off-white">
        <div class="public-container">
            <?php if (isset($component)) { $__componentOriginalf78519ad210db5d56149fa0f4b185795 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf78519ad210db5d56149fa0f4b185795 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.section-heading','data' => ['badge' => 'Get In Touch','title' => 'Ready To Bring Your Ideas To Life?','description' => 'Whether you need business cards, packaging, brochures, banners, corporate branding or nationwide print campaigns, our team is ready to help.','align' => 'center']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.section-heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['badge' => 'Get In Touch','title' => 'Ready To Bring Your Ideas To Life?','description' => 'Whether you need business cards, packaging, brochures, banners, corporate branding or nationwide print campaigns, our team is ready to help.','align' => 'center']); ?>
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
    </div>

    
    <div class="public-conversion-banner">
        <div class="public-conversion-banner__bg">
            <?php if (isset($component)) { $__componentOriginal3a97f469f14669ba552e1c5a424bcd29 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a97f469f14669ba552e1c5a424bcd29 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.media-image','data' => ['src' => $primaryCta['image'],'alt' => $primaryCta['alt'],'fallback' => 'print_press','class' => 'h-full w-full object-cover']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.media-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($primaryCta['image']),'alt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($primaryCta['alt']),'fallback' => 'print_press','class' => 'h-full w-full object-cover']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3a97f469f14669ba552e1c5a424bcd29)): ?>
<?php $attributes = $__attributesOriginal3a97f469f14669ba552e1c5a424bcd29; ?>
<?php unset($__attributesOriginal3a97f469f14669ba552e1c5a424bcd29); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3a97f469f14669ba552e1c5a424bcd29)): ?>
<?php $component = $__componentOriginal3a97f469f14669ba552e1c5a424bcd29; ?>
<?php unset($__componentOriginal3a97f469f14669ba552e1c5a424bcd29); ?>
<?php endif; ?>
            <div class="public-conversion-banner__overlay"></div>
            <div class="public-conversion-banner__glow" aria-hidden="true"></div>
        </div>
        <div class="public-container relative">
            <div class="public-conversion-banner__content" data-animate="fade-up">
                <h3 class="public-conversion-banner__headline"><?php echo e($primaryCta['headline']); ?></h3>
                <p class="public-conversion-banner__text"><?php echo e($primaryCta['description']); ?></p>
                <div class="public-conversion-banner__actions">
                    <?php if (isset($component)) { $__componentOriginalab8f5d6997167bf1906093658ed3789d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab8f5d6997167bf1906093658ed3789d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.button','data' => ['href' => ''.e($quoteFormHref).'','variant' => 'gradient','size' => 'lg','class' => 'public-btn--glow']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e($quoteFormHref).'','variant' => 'gradient','size' => 'lg','class' => 'public-btn--glow']); ?>
                        Request Quote
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
                    <a href="<?php echo e($whatsappUrl); ?>" target="_blank" rel="noopener noreferrer" class="public-conversion-whatsapp-btn public-conversion-whatsapp-btn--light">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Talk To Us On WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>

    
    <div class="public-section bg-white">
        <div class="public-container">
            <h3 class="public-conversion__subtitle text-center" data-animate="fade-up">
                How Would You Like To Proceed?
            </h3>
            <div class="public-conversion-actions">
                <?php $__currentLoopData = $actionCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginal9795f5583e4d4967de41fddaf5ed4725 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9795f5583e4d4967de41fddaf5ed4725 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.conversion-action-card','data' => ['card' => $card,'whatsappUrl' => $whatsappUrl,'dataAnimateDelay' => min($index + 1, 5)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.conversion-action-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['card' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($card),'whatsapp-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($whatsappUrl),'data-animate-delay' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(min($index + 1, 5))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9795f5583e4d4967de41fddaf5ed4725)): ?>
<?php $attributes = $__attributesOriginal9795f5583e4d4967de41fddaf5ed4725; ?>
<?php unset($__attributesOriginal9795f5583e4d4967de41fddaf5ed4725); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9795f5583e4d4967de41fddaf5ed4725)): ?>
<?php $component = $__componentOriginal9795f5583e4d4967de41fddaf5ed4725; ?>
<?php unset($__componentOriginal9795f5583e4d4967de41fddaf5ed4725); ?>
<?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    
    <div class="public-section bg-brand-off-white">
        <div class="public-container">
            <div class="public-conversion-wa" data-animate="fade-up">
                <div class="public-conversion-wa__chat">
                    <div class="public-conversion-wa__bubble public-conversion-wa__bubble--in">
                        <span class="public-conversion-wa__bubble-label">Jana Prints</span>
                        Hi! How can we help with your print project today?
                    </div>
                    <div class="public-conversion-wa__bubble public-conversion-wa__bubble--out">
                        I need a quote for business cards and brochures.
                    </div>
                    <div class="public-conversion-wa__bubble public-conversion-wa__bubble--in">
                        <span class="public-conversion-wa__bubble-label">Jana Prints</span>
                        We'd love to help! Share your quantity and deadline and we'll respond shortly.
                    </div>
                </div>
                <div class="public-conversion-wa__content">
                    <?php if (isset($component)) { $__componentOriginald62171ec7a93eaefc60eace939c26887 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald62171ec7a93eaefc60eace939c26887 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.badge','data' => ['variant' => 'magenta','class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'magenta','class' => 'mb-4']); ?>Quick Response <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald62171ec7a93eaefc60eace939c26887)): ?>
<?php $attributes = $__attributesOriginald62171ec7a93eaefc60eace939c26887; ?>
<?php unset($__attributesOriginald62171ec7a93eaefc60eace939c26887); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald62171ec7a93eaefc60eace939c26887)): ?>
<?php $component = $__componentOriginald62171ec7a93eaefc60eace939c26887; ?>
<?php unset($__componentOriginald62171ec7a93eaefc60eace939c26887); ?>
<?php endif; ?>
                    <h3 class="public-conversion-wa__title">Need A Quick Response?</h3>
                    <p class="public-conversion-wa__text">Chat with our team and get immediate assistance.</p>
                    <ul class="public-conversion-wa__meta">
                        <li>
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span><strong>Response time:</strong> <?php echo e($whatsapp['response_time']); ?></span>
                        </li>
                        <li>
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span><strong>Business hours:</strong> <?php echo e($whatsapp['hours']); ?></span>
                        </li>
                        <li>
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span><strong>Support:</strong> <?php echo e($whatsapp['availability']); ?></span>
                        </li>
                    </ul>
                    <a href="<?php echo e($whatsappUrl); ?>" target="_blank" rel="noopener noreferrer" class="public-conversion-whatsapp-btn public-conversion-whatsapp-btn--pulse">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Start WhatsApp Chat
                    </a>
                </div>
            </div>
        </div>
    </div>

    
    <div class="public-section bg-white">
        <div class="public-container">
            <h3 class="public-conversion__subtitle text-center" data-animate="fade-up">
                Visit, Call Or Email Us
            </h3>
            <div class="public-conversion-contact">
                <div class="public-conversion-contact__cards" data-animate="fade-up">
                    <article class="public-conversion-contact-card">
                        <span class="public-conversion-contact-card__icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </span>
                        <h4 class="public-conversion-contact-card__title">Office Location</h4>
                        <p class="public-conversion-contact-card__value"><?php echo e($contact['address']); ?></p>
                        <p class="public-conversion-contact-card__detail"><?php echo e($contact['address_detail']); ?></p>
                    </article>
                    <article class="public-conversion-contact-card">
                        <span class="public-conversion-contact-card__icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </span>
                        <h4 class="public-conversion-contact-card__title">Phone</h4>
                        <a href="<?php echo e($contact['phone_href']); ?>" class="public-conversion-contact-card__link"><?php echo e($contact['phone']); ?></a>
                    </article>
                    <article class="public-conversion-contact-card">
                        <span class="public-conversion-contact-card__icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </span>
                        <h4 class="public-conversion-contact-card__title">Email</h4>
                        <a href="<?php echo e($contact['email_href']); ?>" class="public-conversion-contact-card__link"><?php echo e($contact['email']); ?></a>
                    </article>
                    <article class="public-conversion-contact-card">
                        <span class="public-conversion-contact-card__icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <h4 class="public-conversion-contact-card__title">Business Hours</h4>
                        <p class="public-conversion-contact-card__value"><?php echo e($contact['hours']); ?></p>
                        <p class="public-conversion-contact-card__detail"><?php echo e($contact['hours_weekend']); ?></p>
                    </article>
                </div>

                <?php if (isset($component)) { $__componentOriginal3b0c8d7c080b8e1a648e2da1bbd92c08 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3b0c8d7c080b8e1a648e2da1bbd92c08 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.contact-map','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.contact-map'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3b0c8d7c080b8e1a648e2da1bbd92c08)): ?>
<?php $attributes = $__attributesOriginal3b0c8d7c080b8e1a648e2da1bbd92c08; ?>
<?php unset($__attributesOriginal3b0c8d7c080b8e1a648e2da1bbd92c08); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3b0c8d7c080b8e1a648e2da1bbd92c08)): ?>
<?php $component = $__componentOriginal3b0c8d7c080b8e1a648e2da1bbd92c08; ?>
<?php unset($__componentOriginal3b0c8d7c080b8e1a648e2da1bbd92c08); ?>
<?php endif; ?>

                <?php if(count($branches)): ?>
                    <div class="public-conversion-branches" data-animate="fade-up">
                        <h4 class="public-conversion-branches__title">Branch Locations</h4>
                        <div class="public-conversion-branches__grid">
                            <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <article class="public-conversion-branches__item">
                                    <h5><?php echo e($branch['name']); ?></h5>
                                    <p><?php echo e($branch['address']); ?></p>
                                    <?php if(! empty($branch['phone'])): ?>
                                        <a href="tel:<?php echo e(preg_replace('/\s+/', '', $branch['phone'])); ?>"><?php echo e($branch['phone']); ?></a>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (isset($component)) { $__componentOriginalc16f49387bac09e20bea30d8b3ec5c15 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc16f49387bac09e20bea30d8b3ec5c15 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.quote-form','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.quote-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc16f49387bac09e20bea30d8b3ec5c15)): ?>
<?php $attributes = $__attributesOriginalc16f49387bac09e20bea30d8b3ec5c15; ?>
<?php unset($__attributesOriginalc16f49387bac09e20bea30d8b3ec5c15); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc16f49387bac09e20bea30d8b3ec5c15)): ?>
<?php $component = $__componentOriginalc16f49387bac09e20bea30d8b3ec5c15; ?>
<?php unset($__componentOriginalc16f49387bac09e20bea30d8b3ec5c15); ?>
<?php endif; ?>

    
    <div class="public-section bg-brand-off-white">
        <div class="public-container">
            <?php if (isset($component)) { $__componentOriginalf78519ad210db5d56149fa0f4b185795 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf78519ad210db5d56149fa0f4b185795 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.section-heading','data' => ['title' => 'Frequently Asked Questions','align' => 'center','class' => '!mb-12']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.section-heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Frequently Asked Questions','align' => 'center','class' => '!mb-12']); ?>
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
            <div class="public-conversion-faq" data-conversion-faq>
                <?php $__currentLoopData = $faq; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginal8bb16dab9c4d3cebab511065a5b661b5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8bb16dab9c4d3cebab511065a5b661b5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.conversion-faq-item','data' => ['item' => $item,'index' => $index,'open' => $index === 0]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.conversion-faq-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['item' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item),'index' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($index),'open' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($index === 0)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8bb16dab9c4d3cebab511065a5b661b5)): ?>
<?php $attributes = $__attributesOriginal8bb16dab9c4d3cebab511065a5b661b5; ?>
<?php unset($__attributesOriginal8bb16dab9c4d3cebab511065a5b661b5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8bb16dab9c4d3cebab511065a5b661b5)): ?>
<?php $component = $__componentOriginal8bb16dab9c4d3cebab511065a5b661b5; ?>
<?php unset($__componentOriginal8bb16dab9c4d3cebab511065a5b661b5); ?>
<?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    
    <div class="public-conversion-trust">
        <div class="public-container">
            <div class="public-conversion-trust__strip" data-animate="fade-up">
                <?php $__currentLoopData = $trustStrip; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'public-conversion-trust__item',
                        'max-lg:hidden' => ($item['type'] ?? '') === 'counter',
                    ]); ?>">
                        <?php if(($item['type'] ?? '') === 'counter'): ?>
                            <span class="public-conversion-trust__value">
                                <?php if (isset($component)) { $__componentOriginal90ae1e1cad06f688995159972e707a80 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal90ae1e1cad06f688995159972e707a80 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.counter','data' => ['value' => $item['value'],'suffix' => $item['suffix'] ?? '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.counter'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['value']),'suffix' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['suffix'] ?? '')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal90ae1e1cad06f688995159972e707a80)): ?>
<?php $attributes = $__attributesOriginal90ae1e1cad06f688995159972e707a80; ?>
<?php unset($__attributesOriginal90ae1e1cad06f688995159972e707a80); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal90ae1e1cad06f688995159972e707a80)): ?>
<?php $component = $__componentOriginal90ae1e1cad06f688995159972e707a80; ?>
<?php unset($__componentOriginal90ae1e1cad06f688995159972e707a80); ?>
<?php endif; ?>
                            </span>
                        <?php else: ?>
                            <span class="public-conversion-trust__check" aria-hidden="true">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </span>
                        <?php endif; ?>
                        <span class="public-conversion-trust__label"><?php echo e($item['label']); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\public\conversion-section.blade.php ENDPATH**/ ?>
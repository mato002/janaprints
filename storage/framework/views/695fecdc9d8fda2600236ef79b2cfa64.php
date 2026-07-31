<?php
    $contact = $websiteContact ?? config('conversion.contact');
    $whatsapp = $websiteWhatsapp ?? config('conversion.whatsapp');
    $whatsappUrl = $websiteWhatsappUrl ?? app(\App\Support\Website\PublicWebsiteContent::class)->whatsappUrl();

    $detailItems = [
        [
            'label' => 'Office Location',
            'value' => $contact['address'],
            'hint' => $contact['address_detail'],
            'icon' => 'location',
            'href' => null,
        ],
        [
            'label' => 'Phone',
            'value' => $contact['phone'],
            'hint' => null,
            'icon' => 'phone',
            'href' => $contact['phone_href'],
        ],
        [
            'label' => 'Email',
            'value' => $contact['email'],
            'hint' => null,
            'icon' => 'email',
            'href' => $contact['email_href'],
        ],
        [
            'label' => 'Business Hours',
            'value' => $contact['hours'],
            'hint' => $contact['hours_weekend'],
            'icon' => 'clock',
            'href' => null,
        ],
    ];
?>

<section id="contact" class="public-contact-section public-section bg-white" data-testid="homepage-contact" data-reveal-section aria-label="Contact Jana Prints">
    <div class="public-container">
        <div class="public-contact-section__intro" data-animate="fade-up">
            <?php if (isset($component)) { $__componentOriginalf78519ad210db5d56149fa0f4b185795 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf78519ad210db5d56149fa0f4b185795 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.section-heading','data' => ['badge' => 'Contact','title' => 'Talk To Jana Prints','description' => 'Request a quote, send artwork, ask a question, or book a consultation with our team.','align' => 'center']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.section-heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['badge' => 'Contact','title' => 'Talk To Jana Prints','description' => 'Request a quote, send artwork, ask a question, or book a consultation with our team.','align' => 'center']); ?>
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

        
        <div class="public-contact-quick-actions lg:hidden" data-animate="fade-up">
            <a href="<?php echo e($quoteFormHref); ?>" class="public-contact-quick-actions__btn public-contact-quick-actions__btn--quote">
                Request Quote
            </a>
            <a href="<?php echo e($whatsappUrl); ?>" target="_blank" rel="noopener noreferrer" class="public-contact-quick-actions__btn public-contact-quick-actions__btn--wa">
                WhatsApp
            </a>
            <a href="<?php echo e($contact['phone_href']); ?>" class="public-contact-quick-actions__btn public-contact-quick-actions__btn--call">
                Call
            </a>
        </div>

        <div class="public-contact-section__layout">
            
            <div class="public-contact-section__aside" data-animate="fade-up">
                <article class="public-contact-details-card public-card public-card--soft">
                    <h3 class="font-display text-lg font-bold text-brand-navy">Contact Details</h3>

                    <ul class="public-contact-details-card__list">
                        <?php $__currentLoopData = $detailItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="public-contact-detail-item">
                                <span class="public-contact-icon-bubble" aria-hidden="true">
                                    <?php switch($item['icon']):
                                        case ('location'): ?>
                                            <svg class="public-contact-icon" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <?php break; ?>
                                        <?php case ('phone'): ?>
                                            <svg class="public-contact-icon" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            <?php break; ?>
                                        <?php case ('email'): ?>
                                            <svg class="public-contact-icon" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            <?php break; ?>
                                        <?php default: ?>
                                            <svg class="public-contact-icon" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <?php endswitch; ?>
                                </span>
                                <div class="public-contact-detail-item__body">
                                    <p class="public-contact-detail-item__label"><?php echo e($item['label']); ?></p>
                                    <?php if($item['href']): ?>
                                        <a href="<?php echo e($item['href']); ?>" class="public-contact-detail-item__value public-contact-detail-item__value--link"><?php echo e($item['value']); ?></a>
                                    <?php else: ?>
                                        <p class="public-contact-detail-item__value"><?php echo e($item['value']); ?></p>
                                    <?php endif; ?>
                                    <?php if($item['hint']): ?>
                                        <p class="public-contact-detail-item__hint"><?php echo e($item['hint']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </article>

                <article class="public-contact-whatsapp-card public-card public-card--soft">
                    <div class="public-contact-whatsapp-card__header">
                        <span class="public-contact-icon-bubble public-contact-icon-bubble--wa" aria-hidden="true">
                            <svg class="public-contact-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </span>
                        <div>
                            <h4 class="font-display text-lg font-bold text-brand-navy">WhatsApp Support</h4>
                            <p class="mt-1 text-sm text-brand-text-secondary">Talk to our team during business hours.</p>
                        </div>
                    </div>

                    <ul class="public-contact-whatsapp-card__meta">
                        <li>
                            <svg class="public-contact-icon" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span><strong>Response time:</strong> <?php echo e($whatsapp['response_time']); ?></span>
                        </li>
                        <li>
                            <svg class="public-contact-icon" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span><strong>Support:</strong> <?php echo e($whatsapp['availability']); ?></span>
                        </li>
                    </ul>

                    <a href="<?php echo e($whatsappUrl); ?>" target="_blank" rel="noopener noreferrer" class="public-conversion-whatsapp-btn public-conversion-whatsapp-btn--pulse public-contact-whatsapp-card__btn">
                        <svg class="public-contact-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Start WhatsApp Chat
                    </a>
                </article>
            </div>

            
            <div class="public-contact-section__form" data-animate="fade-up" data-animate-delay="1">
                <div class="public-card public-card--soft public-contact-section__form-card">
                    <h3 class="font-display text-xl font-bold text-brand-navy">Send us a message</h3>
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
        </div>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/public/contact-section.blade.php ENDPATH**/ ?>
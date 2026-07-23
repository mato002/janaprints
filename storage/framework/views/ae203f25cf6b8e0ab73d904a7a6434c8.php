<?php
    $site = $websiteSite ?? config('site');
    $contact = $websiteContact ?? config('conversion.contact');
    $footer = $websiteFooter ?? config('site.footer');
    $whatsappUrl = $websiteWhatsappUrl ?? app(\App\Support\Website\PublicWebsiteContent::class)->whatsappUrl();
?>

<footer class="public-footer" data-testid="homepage-footer" itemscope itemtype="https://schema.org/Organization">
    <meta itemprop="name" content="<?php echo e($site['name']); ?>">
    <meta itemprop="url" content="<?php echo e($site['url']); ?>">

    <div class="public-footer__ambient" aria-hidden="true"></div>

    <div class="public-container public-section--compact">
        <div class="public-footer__grid grid gap-10 lg:grid-cols-12 lg:gap-12">
            
            <div class="public-footer__brand lg:col-span-4">
                <div class="public-footer__logo-wrap mb-5">
                    <?php if (isset($component)) { $__componentOriginala8631124f8a79f981399d6e3c172e3b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8631124f8a79f981399d6e3c172e3b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.brand-logo','data' => ['full' => true,'size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.brand-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['full' => true,'size' => 'lg']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8631124f8a79f981399d6e3c172e3b3)): ?>
<?php $attributes = $__attributesOriginala8631124f8a79f981399d6e3c172e3b3; ?>
<?php unset($__attributesOriginala8631124f8a79f981399d6e3c172e3b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8631124f8a79f981399d6e3c172e3b3)): ?>
<?php $component = $__componentOriginala8631124f8a79f981399d6e3c172e3b3; ?>
<?php unset($__componentOriginala8631124f8a79f981399d6e3c172e3b3); ?>
<?php endif; ?>
                </div>
                <p class="public-footer__description max-w-sm text-sm leading-relaxed"><?php echo e($footer['tagline']); ?></p>

                <div class="public-footer__badges">
                    <?php $__currentLoopData = $footer['trust_badges']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $badge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="public-footer__badge"><?php echo e($badge); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            
            <nav class="lg:col-span-2" aria-label="Footer navigation">
                <h3 class="public-footer__heading">Explore</h3>
                <ul class="public-footer__links">
                    <?php $__currentLoopData = $footer['nav']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><a href="<?php echo e($link['href']); ?>"><?php echo e($link['label']); ?></a></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </nav>

            
            <div class="public-footer__contact-block lg:col-span-3" itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
                <h3 class="public-footer__heading">Contact</h3>
                <ul class="public-footer__contact">
                    <li>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span itemprop="streetAddress"><?php echo e($contact['address']); ?></span>
                    </li>
                    <li>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <a href="<?php echo e($contact['phone_href']); ?>" itemprop="telephone"><?php echo e($contact['phone']); ?></a>
                    </li>
                    <li>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <a href="<?php echo e($contact['email_href']); ?>" itemprop="email"><?php echo e($contact['email']); ?></a>
                    </li>
                </ul>
                <p class="mt-4 text-xs text-white/45"><?php echo e($contact['hours']); ?> · <?php echo e($contact['hours_weekend']); ?></p>
            </div>

            
            <div class="public-footer__social-block lg:col-span-3">
                <h3 class="public-footer__heading">Follow Us</h3>
                <ul class="public-footer__social">
                    <?php $__currentLoopData = $footer['social']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $social): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>
                            <a href="<?php echo e($social['href']); ?>" aria-label="<?php echo e($social['label']); ?>" rel="noopener noreferrer">
                                <?php switch($social['icon']):
                                    case ('instagram'): ?>
                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                                        <?php break; ?>
                                    <?php case ('linkedin'): ?>
                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 114.127 0 2.063 2.063 0 01-2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                        <?php break; ?>
                                    <?php case ('twitter'): ?>
                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                        <?php break; ?>
                                    <?php default: ?>
                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                <?php endswitch; ?>
                                <span><?php echo e($social['label']); ?></span>
                            </a>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>

                <div class="public-footer__quick-actions mt-6 flex flex-wrap gap-3">
                    <a href="<?php echo e($whatsappUrl); ?>" target="_blank" rel="noopener noreferrer" class="public-footer__quick-link">
                        WhatsApp
                    </a>
                    <a href="<?php echo e($contact['phone_href']); ?>" class="public-footer__quick-link">
                        Call Us
                    </a>
                </div>
            </div>
        </div>

        <div class="public-divider mt-12 opacity-20"></div>

        <div class="public-footer__bottom mt-8 flex flex-col items-center justify-between gap-4 text-xs sm:flex-row">
            <p><?php echo e($websiteFooterCopyright ?? ('© '.date('Y').' '.$site['name'].'. All rights reserved.')); ?></p>
            <p class="text-white/40"><?php echo e($site['tagline']); ?><?php if(! empty($footer['location_suffix'])): ?> — <?php echo e($footer['location_suffix']); ?><?php endif; ?></p>
        </div>
    </div>
</footer>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/public/footer.blade.php ENDPATH**/ ?>
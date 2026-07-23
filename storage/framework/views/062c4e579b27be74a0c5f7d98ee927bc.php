<?php
    $contact = $websiteContact ?? config('conversion.contact');
    $mapEmbed = $contact['map_embed'] ?? null;
    $mapEnabled = $contact['map_enabled'] ?? true;

    if ($mapEnabled && ! $mapEmbed && isset($contact['map_latitude'], $contact['map_longitude'])) {
        $mapEmbed = sprintf(
            'https://maps.google.com/maps?q=%F,%F&z=%d&hl=en&ie=UTF8&iwloc=&output=embed',
            $contact['map_latitude'],
            $contact['map_longitude'],
            $contact['map_zoom'] ?? 15,
        );
    }
?>

<?php if($mapEnabled && $mapEmbed): ?>
    <div class="public-contact-map" data-animate="fade-up">
        <div class="public-contact-map__frame">
            <iframe
                src="<?php echo e($mapEmbed); ?>"
                title="Jana Prints location map"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                allowfullscreen
            ></iframe>

            <div class="public-contact-map__overlay">
                <div class="public-contact-map__title">
                    <span class="public-contact-icon-bubble" aria-hidden="true">
                        <svg class="public-contact-icon" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                    <h3 class="font-display text-base font-bold text-brand-navy sm:text-lg">Find Us</h3>
                </div>
                <p class="mt-2 text-sm font-medium text-brand-navy"><?php echo e($contact['address']); ?></p>
                <?php if(! empty($contact['address_detail'])): ?>
                    <p class="mt-1 text-xs text-brand-text-muted sm:text-sm"><?php echo e($contact['address_detail']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php else: ?>
    <article class="public-card public-card--soft p-6 sm:p-8" data-animate="fade-up" data-contact-location-fallback>
        <div class="flex items-start gap-4">
            <span class="public-contact-icon-bubble" aria-hidden="true">
                <svg class="public-contact-icon" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </span>
            <div>
                <h3 class="font-display text-lg font-bold text-brand-navy">Visit Jana Prints</h3>
                <p class="mt-2 text-sm font-medium text-brand-navy"><?php echo e($contact['address']); ?></p>
                <?php if(! empty($contact['address_detail'])): ?>
                    <p class="mt-1 text-sm text-brand-text-secondary"><?php echo e($contact['address_detail']); ?></p>
                <?php endif; ?>
                <?php if(! empty($contact['map_placeholder'])): ?>
                    <p class="mt-2 text-xs text-brand-text-muted"><?php echo e($contact['map_placeholder']); ?></p>
                <?php endif; ?>
                <?php if(! empty($contact['map_url'])): ?>
                    <a href="<?php echo e($contact['map_url']); ?>" target="_blank" rel="noopener noreferrer" class="public-link mt-4 inline-flex text-sm">
                        Open in Google Maps
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </article>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/public/contact-map.blade.php ENDPATH**/ ?>
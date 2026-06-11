<?php

/**
 * Baseline public website content — fallback mirror of current static/config sources.
 * Used only by WebsiteContentBaselineSeeder / website:content-baseline.
 * Does not replace config/site.php, config/conversion.php, or config/public-images.php.
 *
 * Media slots are merged at runtime with entries from website_cms.media_slots
 * and discovered sections (services, inside-jana, workflow, gallery-preview, testimonials).
 */
return [

    'media_sections' => [
        'hero' => 'Hero Collage',
        'services' => 'Services & Products',
        'inside_jana' => 'Inside Jana Prints',
        'workflow' => 'Production Workflow',
        'facility' => 'Facility',
        'team' => 'Team',
        'quality' => 'Quality Promise',
        'testimonials' => 'Testimonials',
        'gallery_preview' => 'Recent Work Preview (Static Fallbacks)',
        'branding' => 'Branding Reference',
        'cta' => 'CTA & Conversion',
    ],

    /**
     * Additional media slots not covered by website_cms.media_slots.
     * Each entry: section, label, fallback_path, alt, sort_order
     */
    'media_slots' => [
        'inside_jana.production-floor' => [
            'section' => 'inside_jana',
            'label' => 'Inside Jana — Production Floor',
            'image_key' => 'production_floor',
            'alt' => 'Jana Prints production floor with commercial printing equipment',
            'sort_order' => 10,
        ],
        'inside_jana.artwork-prepress' => [
            'section' => 'inside_jana',
            'label' => 'Inside Jana — Artwork & Pre-Press',
            'image_key' => 'prepress',
            'alt' => 'Artwork review and pre-press preparation at Jana Prints',
            'sort_order' => 20,
        ],
        'inside_jana.finishing-packaging-dispatch' => [
            'section' => 'inside_jana',
            'label' => 'Inside Jana — Finishing, Packaging & Dispatch',
            'image_key' => 'delivery',
            'alt' => 'Packaging and dispatch area for finished print orders',
            'sort_order' => 30,
        ],
        'workflow.artwork-review' => [
            'section' => 'workflow',
            'label' => 'Workflow — Artwork Review',
            'image_key' => 'artwork',
            'alt' => 'Artwork review and pre-flight checking',
            'sort_order' => 10,
        ],
        'workflow.pre-press' => [
            'section' => 'workflow',
            'label' => 'Workflow — Pre-Press',
            'image_key' => 'prepress',
            'alt' => 'Pre-press preparation and file setup',
            'sort_order' => 20,
        ],
        'workflow.printing' => [
            'section' => 'workflow',
            'label' => 'Workflow — Printing',
            'image_key' => 'print_press',
            'alt' => 'Commercial printing in progress',
            'sort_order' => 30,
        ],
        'workflow.finishing' => [
            'section' => 'workflow',
            'label' => 'Workflow — Finishing',
            'image_key' => 'finishing',
            'alt' => 'Print finishing and trimming operations',
            'sort_order' => 40,
        ],
        'workflow.quality-control' => [
            'section' => 'workflow',
            'label' => 'Workflow — Quality Control',
            'image_key' => 'quality',
            'alt' => 'Quality control inspection checkpoint',
            'sort_order' => 50,
        ],
        'workflow.packaging' => [
            'section' => 'workflow',
            'label' => 'Workflow — Packaging',
            'image_key' => 'packaging',
            'alt' => 'Order packaging and preparation',
            'sort_order' => 60,
        ],
        'workflow.delivery' => [
            'section' => 'workflow',
            'label' => 'Workflow — Delivery',
            'image_key' => 'delivery',
            'alt' => 'Dispatch and delivery handover',
            'sort_order' => 70,
        ],
        'branding.logo' => [
            'section' => 'branding',
            'label' => 'Storefront Logo (reference — managed in ERP Branding)',
            'fallback_path' => '/images/jana-prints-logo.png',
            'alt' => 'Jana Prints logo',
            'sort_order' => 10,
        ],
        'branding.favicon' => [
            'section' => 'branding',
            'label' => 'Favicon (reference — managed in ERP Branding)',
            'fallback_path' => '/images/logo-mark.png',
            'alt' => 'Jana Prints logo mark',
            'sort_order' => 20,
        ],
        'cta.primary_banner' => [
            'section' => 'cta',
            'label' => 'Primary CTA Banner Image',
            'image_key' => 'print_press',
            'alt' => 'Premium commercial printing production',
            'sort_order' => 10,
        ],
        'seo.og_image' => [
            'section' => 'branding',
            'label' => 'Default Open Graph Image',
            'fallback_path' => '/images/og-jana-prints.svg',
            'alt' => 'Jana Prints Open Graph preview image',
            'sort_order' => 30,
        ],
        'gallery.og_image' => [
            'section' => 'gallery_preview',
            'label' => 'Gallery Page Open Graph Image',
            'fallback_path' => '/images/storefront/gallery/print-production.jpg',
            'alt' => 'Jana Prints gallery Open Graph image',
            'sort_order' => 5,
        ],
        'testimonial.featured.sarah' => [
            'section' => 'testimonials',
            'label' => 'Featured Testimonial — Sarah M.',
            'image_key' => 'brochure',
            'alt' => 'Corporate annual report brochure printing sample',
            'sort_order' => 50,
        ],
        'testimonial.featured.james' => [
            'section' => 'testimonials',
            'label' => 'Featured Testimonial — James K.',
            'image_key' => 'brochure',
            'alt' => 'School prospectus printing project sample',
            'sort_order' => 60,
        ],
        'testimonial.featured.grace' => [
            'section' => 'testimonials',
            'label' => 'Featured Testimonial — Grace W.',
            'image_key' => 'flyers',
            'alt' => 'NGO campaign flyer printing sample',
            'sort_order' => 70,
        ],
        'testimonial.featured.david' => [
            'section' => 'testimonials',
            'label' => 'Featured Testimonial — David O.',
            'image_key' => 'banner',
            'alt' => 'Event banner and signage printing sample',
            'sort_order' => 80,
        ],
        'testimonial.video.school' => [
            'section' => 'testimonials',
            'label' => 'Video Testimonial — School Principal',
            'image_key' => 'school',
            'alt' => 'School principal video testimonial placeholder',
            'sort_order' => 90,
        ],
        'testimonial.video.ngo' => [
            'section' => 'testimonials',
            'label' => 'Video Testimonial — NGO Officer',
            'image_key' => 'team',
            'alt' => 'NGO procurement officer video testimonial placeholder',
            'sort_order' => 100,
        ],
        'testimonial.video.corporate' => [
            'section' => 'testimonials',
            'label' => 'Video Testimonial — Corporate Manager',
            'image_key' => 'office',
            'alt' => 'Corporate marketing manager video testimonial placeholder',
            'sort_order' => 110,
        ],
        'testimonial.video.retail' => [
            'section' => 'testimonials',
            'label' => 'Video Testimonial — Retail Owner',
            'image_key' => 'corporate',
            'alt' => 'Retail business owner video testimonial placeholder',
            'sort_order' => 120,
        ],
    ],

    /**
     * Baseline settings values mirrored from current static config.
     * Keys must exist in website_cms.settings schema (or be added there).
     */
    'settings' => [
        'site.name' => null,
        'site.tagline' => null,
        'footer.tagline' => null,
        'footer.nav' => null,
        'footer.trust_badges' => null,
        'footer.social' => null,
        'footer.copyright' => null,
        'footer.location_suffix' => null,
        'contact.phone' => null,
        'contact.phone_href' => null,
        'contact.email' => null,
        'contact.email_href' => null,
        'contact.address' => null,
        'contact.address_detail' => null,
        'contact.hours' => null,
        'contact.hours_weekend' => null,
        'contact.map_latitude' => null,
        'contact.map_longitude' => null,
        'contact.map_zoom' => null,
        'contact.map_embed' => null,
        'contact.map_url' => null,
        'contact.map_placeholder' => null,
        'whatsapp.number' => null,
        'whatsapp.message' => null,
        'social.facebook' => null,
        'social.instagram' => null,
        'social.linkedin' => null,
        'social.twitter' => null,
        'seo.title' => null,
        'seo.description' => null,
        'seo.keywords' => null,
        'seo.og_image' => null,
    ],

];
